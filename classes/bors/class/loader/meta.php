<?php

class bors_class_loader_meta extends bors_object
{
	static function cache_updated($object)
	{
		if(!config('object_loader_filemtime_check'))
			return false;

		if(!method_exists($object, 'class_filemtime'))
			return false;

		if(filemtime($object->real_class_file()) > $object->class_filemtime())
			return true;

		if(($file = $object->get("real_class_php_inc_file")) && filemtime($file) > $object->real_class_php_inc_filemtime())
			return true;

		return false;
	}
}
