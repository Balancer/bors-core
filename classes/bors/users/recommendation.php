<?php

class bors_users_recommendation extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'bors_recommendations'; }

	function table_fields()
	{
		return array(
			'id',
			'owner_class_name',
			'owner_id',
			'target_class_name',
			'target_id',
			'comment',
			'create_time' => 'UNIX_TIMESTAMP(`create_time`)',
		);
	}

	function replace_on_new_instance() { return true; }

	static function exists($user, $object)
	{
		return bors_find_first(get_called_class(), array(
			'owner_class_name' => $user->new_class_name(),
			'owner_id' => $user->id(),
			'target_class_name' => $object->new_class_name(),
			'target_id' => $object->id(),
		));
	}

	static function add($object, $user, $comment = NULL)
	{
		return bors_new(get_called_class(), array(
			'owner_class_name' => $user->new_class_name(),
			'owner_id' => $user->id(),
			'target_class_name' => $object->new_class_name(),
			'target_id' => $object->id(),
			'comment' => $comment,
		));
	}
}
