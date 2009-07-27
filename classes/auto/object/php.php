<?php

class auto_object_php extends base_object
{
	function init() { }
	function loaded() { return is_object($this->object()); }
	function can_be_empty() { return false; }
	function nav_name() { return $this->object()->nav_name(); }

	private $object = false;
	function object()
	{
		if($this->object !== false)
			return $this->object;
		
		$data = url_parse($this->id());
		$path = $data['path'];
		$page = 1;
		if(preg_match('!^(.+/)(\d+)\.html$!', $path, $m))
		{
			$path = $m[1];
			$page = $m[2];
		}
		
		$class_path = str_replace('/', '_', trim($path, '/'));
		$class_base = config('classes_auto_base', 'auto_php');
		
		if(!($object = object_load($class_base.'_'.$class_path, $this->id())))
		{
			$class_path = $class_path ? $class_path . '_main' : 'main';
			$object = object_load($class_base.'_'.$class_path, $this->id());
		}

		if(!method_exists($object, 'is_auto_url_mapped_class') || !$object->is_auto_url_mapped_class())
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

	function content()
	{
		return $this->object()->content();
	}
}
