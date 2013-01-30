<?php

class bors_objects_loaders_yaml extends bors_objects_loaders_meta
{
	static function load($class_name, $object_id)
	{
		if(!preg_match('/^\w+$/', $class_name))
			return NULL;

		$file_base = 'classes/'.str_replace('_', '/', $class_name);
		$data = bors_data_yaml::load($file_base.'.yaml');
		if(!$data)
			return NULL;

//		$extends_class_name = popval($data['data'], 'extends', 'bors_objects_meta');
//		$object = new $extends_class_name($object_id);
		$object = new $class_name($object_id);
		$object->data = $data['data'];
		$object->attrs = $data['attrs'];
		$object->class_file = @$data['attrs']['file'];
		$object->class_filemtime = @$data['attrs']['filemtime'];

		$object->_configure();
		$loaded = $object->is_loaded();
		if(is_object($loaded))
			$object = $loaded;

		if(!$loaded)
			$loaded = $object->data_load();

		if(!$object->can_be_empty() && !$object->is_loaded())
			return NULL;

		return $object;
	}
}
