<?php

class bors_objects_version extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'bors_versioning'; }
	function table_fields()
	{
		return array(
			'id',
			'target_class_name',
			'target_id',
			'target_version',
			'property_name',
			'value',
			'create_time',
			'owner_id',
			'moderator_id',
			'is_approved',
		);
	}

	function replace_on_new_instance() { return true; }

	static function load($class_name, $object_id, $version)
	{
		static $cache = array();
		$hash = '__load:'.$class_name.'-'.$object_id.'-'.$version;
		if(!empty($cache[$hash]))
			return $cache[$hash];

		$object = bors_load($class_name, $object_id);
//		echo $object;
		foreach(bors_find_all(__CLASS__, array(
			'target_class_name' => $class_name,
			'target_id' => $object_id,
			'target_version' => $version,
		)) as $x)
		{
//			echo "{$x->property_name()} = {$x->value()}<br/>\n";
			$object->set_attr('versioning_properties',
				array_merge(
					$object->attr('versioning_properties', array()),
					array($x->property_name() => $object->get($x->property_name()))
				)
			);

			$object->set($x->property_name(), $x->value(), false);
//			$object->set_attr('versioning_property', $x->property_name());
		}

		return $cache[$hash] = $object;
	}

	static function remove_all($object)
	{
		foreach(bors_find_all(__CLASS__, array(
			'target_class_name' => $object->extends_class_name(),
			'target_id' => $object->id(),
		)) as $x)
			$x->delete();
	}

	static function save($object, $version)
	{
		$fields = bors_lib_orm::fields($object);
		foreach($object->changed_fields as $property => $original)
		{
			if(!empty($fields[$property]['non_versioning']))
				continue;

			bors_new(__CLASS__, array(
				'target_class_name' => $object->extends_class_name(),
				'target_id' => $object->id(),
				'target_version' => $version,
				'property_name' => $property,
				'value' => $object->get($property),
				'is_approved' => false,
			));

			unset($object->changed_fields[$property]);
		}
	}
}
