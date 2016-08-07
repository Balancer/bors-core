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
			bors_debug::syslog('errors-structure', "Can't load parent '{$parent_id}' for {$object}");
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

		// Ищем методы, перекрываемые переменным по умолчанию
		$m = "_{$name}_def";
		if(method_exists($class_name, $m) && !$skip_methods)
		{
			try { $value = call_user_func(array($class_name, $m)); }
			catch(Exception $e) { $value = NULL; }
			return $value;
		}

		return $default;
	}

	static function get_foo($class_name, $name, $default = NULL)
	{
		static $objects = array();
		$foo = @$objects[$class_name];

		if(!$foo)
		{
			if(!class_exists($class_name))
				return $default;

			$foo = $objects[$class_name] = new $class_name(NULL);

			if(!$foo)
				return $default;
		}

		$foo->b2_configure();
		return $foo->get($name, $default);
	}

	static function parent_lines($object, $level=0)
	{
		if($object->get('b2_no_breadcrumb'))
			return [];

		$parent_lines = [];

		$current_line = array(
			'url' => $object->url(),
			'title' => $object->nav_name()
		);

		if($level == 0)
			$current_line['is_active'] = true;

		foreach($object->parents() as $parent)
		{
			if(is_object($parent))
				$parent_object = $parent;
			else
				$parent_object = object_load($parent);

			if(!$parent_object)
				continue;

			if($parent_object->url() == $object->url())
				continue;

			foreach(self::parent_lines($parent_object, $level+1) as $parent_uplines)
				$parent_lines[] = array_merge($parent_uplines, array($current_line));
		}

		if(empty($parent_lines))
			$parent_lines[] = array($current_line);

		return $parent_lines;
	}
}
