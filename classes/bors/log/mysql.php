<?php

class bors_log_mysql extends base_object_db
{
	function main_table() { return 'bors_logging'; }
	function main_table_fields()
	{
		return array(
			'id',
			'category',
			'type',
			'title',
			'target_class_name',
			'target_object_id',
			'message',
			'create_time',
			'modify_time',
			'owner_id',
			'last_editor_id',
		);
	}

	static function add($data)
	{
		if($target = @$data['target'])
		{
			$data['target_class_name'] = $target->class_name();
			$data['target_object_id'] = $target->id();
			unset($data['target']);
		}

		return bors_new('bors_log_mysql', $data);
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array('target' => 'target_class_name(target_object_id)'));
	}
}
