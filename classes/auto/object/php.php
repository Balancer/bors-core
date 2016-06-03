<?php

class auto_object_php extends bors_object
{
	function data_load() { }
	function is_loaded() { return $this->object(); } // Не приводить к bool, это реально загруженный объект
	function can_be_empty() { return false; }
	function nav_name() { return $this->object()->nav_name(); }

	function access()
	{
		if(($obj = $this->object()))
			return $obj->access();

		return parent::access();
	}

	private $_object = false;
	function object()
	{
		if($this->_object !== false)
			return $this->_object;

		$data = url_parse($this->id());
		$path = $data['path'];
		if(($ut = config('url_truncate')))
			$path = preg_replace("!/$ut/!", '/', $path);

		$page = NULL;
		if(preg_match('!^(.+/)(\d+)\.html$!', $path, $m))
		{
			$path = $m[1];
			$page = $m[2];
		}

		$class_base = NULL;
		if(!empty($GLOBALS['bors_data']['routers']))
		{
			foreach($GLOBALS['bors_data']['routers'] as $base_url => $x)
			{
				$base_class = $x['base_class'];
				if(strpos($path, $base_url) === 0)
				{
					$class_base = $base_class.'_';
					$path = str_replace($base_url, '', $path);
				}
			}
		}

		$class_path = str_replace('/', '_', trim($path, '/'));

		if(!empty(bors::$composer_autoroute_prefixes))
			$class_base = bors::$composer_autoroute_prefixes[0].'_'; //TODO: сделать мультизагрузку. Пока только одиночный класс.

		if(!$class_base)
		{
			if($class_base = config('classes_auto_base', config('project.name', 'auto_php')))
				$class_base .= '_';
		}

		$is_auto = false;
		$object_id = false;

		//	http://forums.balancer.ru/ajax/pagemod/10000.js
		if(preg_match('!^(.+_\d+)\.\w+$!', $class_path, $m))
			$class_path = $m[1];

		//	/users/84018/
		if(preg_match('!^(.+)_(\d+|new)$!', $class_path, $m))
		{
			$object_id = $m[2];
//			var_dump($class_base, $m, $object_id);

			if(is_numeric($object_id) && class_include($class_base.($cp = $m[1].'_view')))
			{
				$class_path = $cp;
				$is_auto = true;
			}
			elseif(is_numeric($object_id) && class_include($class_base.($cp = blib_grammar::singular($m[1]).'_view')))
			{
				$class_path = $cp;
				$is_auto = true;
			}
			elseif(class_include($class_base.($cp = $m[1].'_edit')))
			{
				$class_path = $cp;
			}
			elseif(class_include($class_base.($cp = $m[1].'_'.$m[2].'_main')))
			{
				$class_path = $cp;
				$object_id = NULL;
			}
			elseif(class_include($class_base.($cp = $m[1].'_'.$m[2])))
			{
				$class_path = $cp;
				$object_id = NULL;
			}
			elseif(class_include($class_base.($cp = $m[1])))
			{
				$class_path = $cp;
				$object_id = is_numeric($object_id) ? $object_id : NULL;
			}
			elseif(class_include($class_base.($cp = blib_grammar::chunk_singular($m[1]))))
			{
				$class_path = $cp;
			}
			else
			{
				$class_path = blib_grammar::singular($m[1]);
				$object_id = $m[2];
			}
		}
		elseif(preg_match('!^(\w+)_(\d+)(_\w+)$!', $class_path, $m))
		{
			// /users/84018/votes/ -> project_users_votes_view(84018)
			if(class_include($class_base.($cp = $m[1].$m[3].'_view')))
			{
				$class_path = $cp;
				$object_id = $m[2];
				$is_auto = true;
			}
			elseif(class_include($class_base.($cp = $m[1].$m[3])))
			{
				$class_path = $cp;
				$object_id = $m[2];
				$is_auto = true;
//				var_dump($class_path, $object_id);
			}
			elseif(class_include($class_base.($cp = $m[1].$m[3].'_main'))) // http://aviaport.wrk.ru/directory/airlines/264/monitoring/
			{
				$class_path = $cp;
				$object_id = $m[2];
				$is_auto = true;
//				var_dump($class_path, $object_id);
			}
		}
		// http://admin.aviaport.ru/digest/origins/list.xls -> project_digest_origins_list
		elseif(preg_match('!^(\w+_[a-z0-9]+)\.(\w{1,4})$!', $class_path, $m))
		{
			if(class_include($class_base.($cp = $m[1])))
			{
				$class_path = $cp;
				$object_id = NULL;
			}
		}
		// http://forums.balancer.ru/users/warnings/2.html
		elseif(preg_match('!^(\w+s)$!', $class_path, $m))
		{
			if(class_include($class_base.($cp = $m[1])))
			{
				$class_path = $cp;
				$object_id = NULL;
			}
		}

		if($object_id === false)
			$object_id = $this->id();

		if($object_id == 'new')
			$object_id = NULL;

		if($object_id == $this->called_url())
			$object_id = NULL;

		if(!(class_exists($class_base.$class_path) && ($object = bors_load($class_base.$class_path, $object_id))))
		{
			$class_path = $class_path ? $class_path . '_main' : 'main';
			$object = bors_load($class_base.$class_path, $object_id);
			$is_auto = true;
		}

		if(!($is_auto
				|| config('classes_auto_full_enabled')
				|| object_property($object, 'is_auto_url_mapped_class')
				|| object_property($object, 'auto_map')
				|| object_property($object, 'auto_route')
		))
			$object = NULL;

		if($object)
		{
			$object->set_page($page);
			$object->_set_arg('page', $page);

			$object->set_called_url($this->id());
//			bors()->set_main_object($object);
			if(!$object->parents(true))
				$object->set_parents(array(secure_path(dirname($path).'/')), false);

			bors_function_include('debug/log_var');
			debug_log_var('target_class_file', $object->class_file());
			debug_log_var('loader_class_file', $this->class_file());
		}

		return $this->_object = $object;
	}

	function content() { return $this->object()->content(); }
	function create_time() { return $this->object()->create_time(); }
	function modify_time() { return $this->object()->modify_time(); }

	function action_target() { return $this->object(); }

	function pre_show() { return $this->object->pre_show(); }
	function pre_parse() { return $this->object->pre_parse(); }
}
