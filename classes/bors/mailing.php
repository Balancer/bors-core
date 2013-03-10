<?php

class bors_mailing extends bors_object_db
{
	function replace_on_new_instance() { return true; }

	function db_name() { return 'BORS'; }
	function table_name() { return 'bors_mailing'; }
	function table_fields()
	{
		return array(
			'id',
			'target_class_name',
			'target_object_id',
			'target_user_id',
			'create_time',
		);
	}

	static function add($object, $user_id)
	{
//		debug_hidden_log('test-mailing', "$object -> $user_id");

		object_new_instance('bors_mailing', array(
			'target_class_name' => $object->class_name(),
			'target_object_id' => $object->id(),
			'target_user_id' => $user_id,
		));
	}
}
