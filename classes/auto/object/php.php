<?php

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
		$class_base = config('classes_auto_base', 'auto_php');

		if(preg_match('!^(.+)_(\d+|new)$!', $class_path, $m))
		{
			if(class_include($class_base.'_'.($cp = $m[1].'_edit')))
			{
				$class_path = $cp;
				$object_id = $m[2];
			}
			else
			{
				$class_path = bors_unplural($m[1]);
				$object_id = $m[2];
			}
		}
		else
			$object_id = $this->id();

		if($object_id == 'new')
			$object_id = NULL;

		if(!($object = bors_load($class_base.'_'.$class_path, $object_id)))
		{
			$class_path = $class_path ? $class_path . '_main' : 'main';
			$object = bors_load($class_base.'_'.$class_path, $this->id());
		}

		if(!config('classes_auto_full_enabled') && !object_property($object, 'is_auto_url_mapped_class'))
			$object = NULL;

		if($object)
		{
			$object->set_page($page);
			$object->_set_arg('page', $page);

			$object->set_called_url($this->id());
			bors()->set_main_object($object);
			if(!$object->parents(true))
				$object->set_parents(array(secure_path(dirname($path).'/')), false);

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
