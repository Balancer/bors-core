<?php

bors_function_include('natural/bors_unplural');

class auto_object_php extends base_object
{
	function init() { }
	function loaded() { return $this->object(); }
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

		$class_path = str_replace('/', '_', trim($path, '/'));
		if($class_base = config('classes_auto_base', config('project.name', 'auto_php')))
			$class_base .= '_';

		$is_auto = false;
		if(preg_match('!^(.+)_(\d+|new)$!', $class_path, $m))
		{
			if(class_include($class_base.($cp = $m[1].'_view')))
			{
				$class_path = $cp;
				$object_id = $m[2];
				$is_auto = true;
			}
			elseif(class_include($class_base.($cp = bors_unplural($m[1]).'_view')))
			{
				$class_path = $cp;
				$object_id = $m[2];
				$is_auto = true;
			}
			elseif(class_include($class_base.($cp = $m[1].'_edit')))
			{
				$class_path = $cp;
				$object_id = $m[2];
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
			else
			{
				$class_path = bors_unplural($m[1]);
				$object_id = $m[2];
			}
		}
		elseif(preg_match('!^(\w+)_(\d+)(_\w+)$!', $class_path, $m))
		{
			if(class_include($class_base.($cp = $m[1].$m[3])))
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
			else
				$object_id = $this->id();
		}
		else
			$object_id = $this->id();

		if($object_id == 'new')
			$object_id = NULL;

		if($object_id == $this->called_url())
			$object_id = NULL;

		if(!($object = bors_load($class_base.$class_path, $object_id)))
		{
			$class_path = $class_path ? $class_path . '_main' : 'main';
			$object = bors_load($class_base.$class_path, $object_id);
		}

		if(!($is_auto
				|| config('classes_auto_full_enabled')
				|| object_property($object, 'is_auto_url_mapped_class')
				|| object_property($object, 'auto_map')
		))
			$object = NULL;

		if($object)
		{
			$object->set_page($page);
			$object->_set_arg('page', $page);

			$object->set_called_url($this->id());
			bors()->set_main_object($object);
			if(!$object->parents(true))
				$object->set_parents(array(secure_path(dirname($path).'/')), false);

			bors_function_include('debug/log_var');
			debug_log_var('target_class_file', $object->class_file());
			debug_log_var('loader_class_file', $this->class_file());
		}

		return $this->_object = $object;
	}

	function content() { return $this->object()->content(); }
	function create_time($exactly = false) { return $this->object()->create_time($exactly); }
	function modify_time($exactly = false) { return $this->object()->modify_time($exactly); }

	function action_target() { return $this->object(); }

	function pre_show() { return $this->object->pre_show(); }
	function pre_parse() { return $this->object->pre_parse(); }
}
