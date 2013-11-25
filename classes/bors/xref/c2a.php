<?php

// Class-to-any. Первый класс определяется только по ID, второй — и по имени тоже.

class bors_xref_c2a extends bors_object_db
{
	static function add($object, $target, $args = array(), $xref_class_name = NULL)
	{
		if(!$xref_class_name)
			$xref_class_name = get_called_class();

		$field_name = self::object_name($xref_class_name).'_id';

//		echo "link $object to $target: $xref_class_name / $field_name\n";

		$args[$field_name] = $object->id();
		$args['target_class_name'] = $target->extends_class_name();
		$args['target_class_id'] = $target->extends_class_id();
		$args['target_object_id'] = $target->id();
		$args['target_is_published'] = $target->get('is_published');
		$args['target_create_time'] = $target->create_time();
		bors_new($xref_class_name, $args);
	}

	static function object_name($xref_class_name)
	{
		if(preg_match('/^\w+?_([a-z]+)_xref_/', $xref_class_name, $m))
			return $m[1];

		return NULL;
	}

	function ignore_on_new_instance() { return true; }
}
