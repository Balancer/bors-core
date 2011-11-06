<?php

class bors_lib_object
{
	static function tree_map($object)
	{
		$parent_id = $object->parent_id();
		if(!$parent_id)
			return '';

		$parent = $object->parent();
		if(!$parent)
		{
			debug_hidden_log('errors-structure', "Can't load parent '{$parent_id}' for {$object}");
			return '';
		}

		return self::tree_map($parent).$parent_id.'.';
	}

	static function get_static($class_name, $name, $default = NULL, $skip_methods = false, $skip_properties = false)
	{
		if(method_exists($class_name, $name) && !$skip_methods)
			return call_user_func(array($class_name, $name));
//			return $class_name::$name();

		// Проверяем одноимённые переменные (var $title = 'Files')
		if(property_exists($class_name, $name) && !$skip_properties)
		{
//			return $class_name::$name;
			$vars = get_class_vars($class_name);
			return @$vars[$name];
		}

		// Проверяем одноимённые переменные, требующие перекодирования (var $title_ec = 'Сообщения')
		$name_ec = "{$name}_ec";
		if(property_exists($class_name, $name_ec) && !$skip_properties)
		{
//			return ec($class_name::$this->$name_ec);
			$vars = get_class_vars($class_name);
			return ec(@$vars[$name_ec]);
		}

		return $default;
	}

	function get_foo($class_name, $name)
	{
		$foo = new $class_name(NULL);
		return $foo->get($name);
	}
}
