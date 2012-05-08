<?php

// Class-to-class. Оба объекта связи задаются только по ID. Классы фиксированы.

class bors_xref_c2c extends bors_object_db
{
	static function add($object, $target, $args = array(), $xref_class_name = NULL)
	{
		if(!$xref_class_name)
			$xref_class_name = get_called_class();

		$object_field_name = self::object_name($xref_class_name).'_id';
		$target_field_name = self::target_name($xref_class_name).'_id';

		$args[$object_field_name] = $object->id();
		$args[$target_field_name] = $target->id();
		bors_new($xref_class_name, $args);
	}

	static function object_name($xref_class_name)
	{
		if(preg_match('/^\w+?_([a-z]+)_xref_/', $xref_class_name, $m))
			return $m[1];

		return NULL;
	}

	static function target_name($xref_class_name)
	{
		if(preg_match('/_xref_([a-z0-9]+)$/', $xref_class_name, $m))
			return $m[1];

		return NULL;
	}

	function ignore_on_new_instance() { return true; }

	function named_list($xref_class_name = NULL)
	{
		require_once('inc/bors/lists.php');

		if(!$xref_class_name)
			$xref_class_name = get_called_class();

		if(is_object($xref_class_name))
		{
			// http://matf.aviaport.ru/companies/1/edit/
			// bors-core/classes/bors/forms/checkbox/list.php:25
			return bors_named_list_db($this->target_class_name());
		}

		return bors_named_list_db($xref_class_name::target_class_name());
	}

	function name($object, $xref_class_name = NULL)
	{
		if(!$xref_class_name)
			$xref_class_name = get_called_class();

		return self::target_name($xref_class_name).'_ids';
	}
}
