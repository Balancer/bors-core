<?php

class bors_mailing extends base_object_db
{
	function main_db() { return 'BORS'; }
	function main_table() { return 'bors_mailing'; }
	function main_table_fields()
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
