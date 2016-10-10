<?php

use B2\Cfg;

class b2
{
	private $__config			= array();
	private $__project_classes	= array();
	private $__projects			= array();
	private $composer			= NULL;

	// Вернуть значение параметра конфигурации
	function conf($name, $default = NULL)
	{
		return array_key_exists($name, $this->__config) ? $this->__config[$name] : $default;
	}

	// Загрузить объект по соответствующему URI роутера
	function load_uri($uri)
	{
		foreach($this->projects() as $project)
		{
			// У каждого проекта — собственный роутер
			if($router = $project->router())
			{
				if($obj = $router->load_uri($uri))
					return $obj;
			}
			else
				echo "Not defined router for project ".get_class($project)."<br/>\n";
		}

		return NULL;
	}

	private function __load_object($class_name, $id, $args)
	{
		$original_id = $id;
		$object = NULL;

		if($id === 'NULL')
			$id = NULL;

		if(!($class_file = bors_class_loader::file($class_name, $this->dirs())))
		{
			if(Cfg::get('throw_exception_on_class_not_found'))
				return bors_throw("Class '$class_name' not found");

			return $object;
		}

		$found = 0;

		if(method_exists($class_name, 'id_prepare'))
			$id = call_user_func(array($class_name, 'id_prepare'), $id, $class_name);

		// id_prepare нам вернул готовый к использованию объект
		if(is_object($id) && !is_object($original_id))
		{
			$object = $id;
			$id = $object->id();
			$found = 2;
		}
		else
		{
			$object = &load_cached_object($class_name, $id, $args, $found);

			if($object && ($object->id() != $id))
			{
				$found = 0;
				delete_cached_object_by_id($class_name, $id);
				$object = NULL;
			}
		}

		if(!$object)
		{
			$found = 0;
			$object = new $class_name($id);
			if(!method_exists($object, 'set_class_file'))
				return NULL;

			$object->set_class_file($class_file);

			if(Cfg::get('debug_objects_create_counting_details'))
			{
				bors_function_include('debug/count_inc');
				debug_count_inc("bors_load($class_name,init)");
			}
		}

		$object->b2_configure();

		$is_loaded = $object->is_loaded();

		if(is_object($is_loaded))
			$object = $is_loaded;

		if(!$is_loaded)
			$is_loaded = $object->data_load();

		if(/*($id || $url) && */!$object->can_be_empty() && !$object->is_loaded())
			return NULL;

		if(!empty($args['need_check_to_public_load']))
		{
			unset($args['need_check_to_public_load']);
			if(!method_exists($object, 'can_public_load') || !$object->can_public_load())
				return NULL;
		}

		if($found != 1 && $object->can_cached())
			save_cached_object($object);

		return $object;
	}

    /**
     * @param string $class_name
     * @param integer|string $id
     * @return b2_object
     * @throws Exception
     */
    function load($class_name, $id = NULL)
	{
		if(is_numeric($class_name))
			$class_name = class_id_to_name($class_name);

		if(Cfg::get('debug_trace_object_load'))
		{
			bors_function_include('debug/hidden_log');
			bors_debug::syslog('objects_load', "$class_name($id)", Cfg::get('debug_trace_object_load_trace'));
		}

		if(!$class_name)
			return NULL;

		if(Cfg::get('debug_objects_create_counting_details'))
			debug_count_inc("bors_load($class_name)");

		$object = $this->__load_object($class_name, $id, array());

		if(!$object)
			$object = bors_objects_loaders_meta::find($class_name, $id);

		if(!$object)
		{
			if(Cfg::get('orm.is_strict') && !class_include($class_name))
				bors_throw("Not found class '{$class_name}' for load with id='{$id}'");

			return NULL;
		}

		$object->set_b2($this);
		return $object;
	}

	function projects() { return $this->__projects; }
	function composer() { return $this->composer; }
	function init()
	{
		if(!empty($GLOBALS['b2.instance']))
			return;

		if(file_exists($cf = dirname(__DIR__).'/config.ini'))
			$this->config_ini($cf);

		if(file_exists($cf = COMPOSER_ROOT.'/config.ini'))
			$this->config_ini($cf);

		$GLOBALS['b2.instance'] = $this;

		if($main_project = $this->conf('project.main'))
			$this->__project_classes[] = $main_project;

		if(class_exists('b2_project'))
			$this->__project_classes[] = 'b2_project';

		foreach($this->__project_classes as $project_class)
		{
			$project = $this->load($project_class);
			if($project)
			{
				$project->set_b2($this);
				$this->__projects[] = $project;
			}
			else
				echo "Unknown project class {$project_class}<br/>\n";
		}

		if(!empty($GLOBALS['bors.composer.class_loader']))
			$this->composer = $GLOBALS['bors.composer.class_loader'];
	}

	/**
	 * @param string $file
	 * Загрузить .ini файл в параметры конфигурации.
	 */
	function config_ini($file)
	{
		$ini_data = parse_ini_file($file, true);

		if($ini_data === false)
			bors_throw("'$file' parse error");

		foreach($ini_data as $section_name => $data)
		{
			if($section_name == 'global' || $section_name == 'config')
				$this->__config = array_merge($this->__config, $data);
			else
				foreach($data as $key => $value)
					$this->__config[$section_name.'.'.$key] = $value;
		}
	}

	function dirs()
	{
		$dirs = array();
		foreach($this->projects() as $project)
		{
			$dirs[] = $project->project_dir();
		}

		return $dirs;
	}

	static function instance()
	{
		if(empty($GLOBALS['b2.instance']))
		{
			if(class_exists('b2f'))
				b2f::init_framework();
			else
				require_once(dirname(__DIR__).'/init.php');

			$b2 = new b2();
			$b2->init();
		}

		return $GLOBALS['b2.instance'];
	}

	static function find($class_name)
	{
		return new b2_core_find($class_name);
	}

	static function tmp_dir() { return Cfg::get('cache_dir'); }

	static function log()
	{
		static $instance = NULL;

		if(!$instance)
			$instance = new b2_logger;

		return $instance;
	}
}

function b2_load($class_name, $id = NULL) { return b2::instance()->load($class_name, $id); }
