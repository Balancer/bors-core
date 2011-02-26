<?php

class bors_common_event extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function db_name() { return 'BALANCER'; }
	function table_name() { return 'common_events'; }
	function table_fields()
	{
		return array(
			'id',
			'title',
			'text',
			'user_class_name',
			'user_id',
			'handler_class_name',
			'object_class_name',
			'object_id',
			'target_class_name',
			'target_id',
			'create_time' 	=> 'UNIX_TIMESTAMP(create_timestamp)',
			'modify_time'	=> 'UNIX_TIMESTAMP(modify_timestamp)',
		);
	}

	function add($action, $object = NULL, $user = NULL, $target = NULL)
	{
//		print_dd(compact('object', 'user', 'target'));
		$actor = bors_load_ex($action, NULL, compact('object', 'user', 'target'));
		return bors_new(__CLASS__, array(
			'title'	=> $actor->title(),
			'text'	=> $actor->text(),
			'user_class_name'	=> object_property($user, 'class_name'),
			'user_id'			=> object_property($user, 'id'),
			'handler_class_name'=> $action,
			'object_class_name'	=> object_property($object, 'class_name'),
			'object_id'			=> object_property($object, 'id'),
			'target_class_name'	=> object_property($target, 'class_name'),
			'target_id'			=> object_property($target, 'id'),
		));
	}
}
