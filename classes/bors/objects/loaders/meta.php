<?php

class bors_objects_loaders_meta extends bors_object
{
	static private $class_loaders = array();

	static function register()
	{
		if(function_exists('get_called_class'))
		{
			$called_class = get_called_class(); // (PHP 5 >= 5.3.0)
//		echo "Register $called_class<br/>";
			self::$class_loaders[] = $called_class;
		}
	}

	static function find($class_name, $object_id)
	{
//		echo "Find $class_name ($object_id)<Br/>\n";
		foreach(self::$class_loaders as $class_loader)
			if($object = call_user_func(array($class_loader, 'load'), $class_name, $object_id))
				return $object;

		return NULL;
	}
}
