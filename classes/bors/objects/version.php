<?php

class bors_objects_version extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
//	function db_name() { return 'BORS'; }
	function table_name() { return 'bors_versioning'; }
	function table_fields()
	{
		return array(
			'id',
			'class_name',
			'object_id',
			'version',
			'property_name',
			'value',
			'create_time',
			'owner_id',
			'moderator_id',
			'is_approved',
		);
	}

	static function load($class_name, $object_id, $version)
	{
		$object = bors_load($class_name, $object_id);
		foreach(bors_find_all(__CLASS__, array(
			'class_name' => $class_name,
			'object_id' => $object_id,
			'version' => $version,
		)) as $x)
			$object[$x->property_name()] = $x->value();

		return $object;
	}

	static function save($class_name, $object_id, $version)
	{
	}

//	static function approve($object, )
}
