<?php

class bors_object_change_request extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'bors_object_change_requests'; }
	function table_fields()
	{
		return array(
			'id',
			'target_class_name',
			'target_object_id',
			'target_field',
			'target_value',
			'object_data_json',
			'target_target_class_name',
			'target_target_object_id',
			'user_id',
			'create_time',
			'is_confirmed',
			'type_title',
		);
	}

	function auto_targets()
	{
		return array(
			'target' => 'target_class_name(target_object_id)',
			'target_target' => 'target_target_class_name(target_target_object_id)',
		);
	}

	function auto_objects()
	{
		return array(
			'owner' => 'bors_user(user_id)',
		);
	}

	// $self_class_name - это костыль из-за отсутствия позднего статического связывания в PHP < 5.3
	static function add($target, $property, $value, $type_title, $user, $self_class_name)
	{
		return object_new_instance($self_class_name, array(
			'target_class_name' => $target->class_name(),
			'target_object_id' => $target->id(),
			'target_field' => $property,
			'target_value' => $value,
			'type_title' => $type_title,
			'user_id' => $user->id(),
		));
	}

	static function add_array($target, $data, $type_title, $user, $self_class_name)
	{
		if(is_object($target))
		{
			$target_class_name = $target->class_name();
			$target_object_id  = $target->id();
			$target_target_class_name = NULL;
			$target_target_object_id  = NULL;
		}
		else
		{
			$target_class_name = $target;
			$target_object_id  = NULL;
			$target_target_class_name = @$data['*target_target_class_name'];
			$target_target_object_id  = @$data['*target_target_object_id'];
			unset($data['*target_target_class_name'], $data['*target_target_object_id']);
		}

		return object_new_instance($self_class_name, array(
			'target_class_name' => $target_class_name,
			'target_object_id' => $target_object_id,
			'object_data_json' => json_encode($data),
			'target_target_class_name' => $target_target_class_name,
			'target_target_object_id'  => $target_target_object_id,
			'type_title' => $type_title,
			'user_id' => $user->id(),
		));
	}

	static function add_mixed($target, $property, $value, $data, $type_title, $user, $self_class_name)
	{
		return object_new_instance($self_class_name, array(
			'target_class_name' => $target->class_name(),
			'target_object_id' => $target->id(),
			'target_field' => $property,
			'target_value' => $value,
			'object_data_json' => json_encode($data),
			'type_title' => $type_title,
			'user_id' => $user->id(),
		));
	}
}
