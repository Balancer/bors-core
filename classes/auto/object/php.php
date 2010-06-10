<?php

class auto_object_php extends base_object
{
	function init() { }
	function loaded() { return is_object($this->object()); }
	function can_be_empty() { return false; }
	function nav_name() { return $this->object()->nav_name(); }

	function access()
	{
		if(($obj = $this->object()))
			return $obj->access();

		return parent::access();
	}

	private $object = false;
	function object()
	{
		if($this->object !== false)
			return $this->object;

		$data = url_parse($this->id());
		$path = $data['path'];
		if(($ut = config('url_truncate')))
			$path = preg_replace("!/$ut/!", '/', $path);

		$page = 1;
		if(preg_match('!^(.+/)(\d+)\.html$!', $path, $m))
		{
			$path = $m[1];
			$page = $m[2];
		}

		$class_path = str_replace('/', '_', trim($path, '/'));
		$class_base = config('classes_auto_base', 'auto_php');

		if(preg_match('!^(.+)_(\d+)$!', $class_path, $m))
		{
			$class_path = bors_unplural($m[1]);
			$object_id = $m[2];
		}
		else
			$object_id = $this->id();

		if(!($object = object_load($class_base.'_'.$class_path, $object_id)))
		{
			$class_path = $class_path ? $class_path . '_main' : 'main';
			$object = object_load($class_base.'_'.$class_path, $this->id());
		}

		if(!config('classes_auto_full_enabled') && !object_property($object, 'is_auto_url_mapped_class'))
			$object = NULL;

		if($object)
		{
			$object->set_page($page);
			$object->set_called_url($this->id());
			bors()->set_main_object($object);
			if(!$object->parents(true))
				$object->set_parents(array(secure_path(dirname($path).'/')), false);
		}

		return $this->object = $object;
	}

	function content() { return $this->object()->content(); }
	function create_time($exactly = false) { return $this->object()->create_time($exactly); }
	function modify_time($exactly = false) { return $this->object()->modify_time($exactly); }

	function action_target() { return $this->object(); }
}
