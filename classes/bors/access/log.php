<?php

class bors_access_log extends base_object_db
{
	function main_table() { return 'bors_access_log'; }
	function main_table_fields()
	{
		return array(
			'id',
			'user_ip',
			'user_id',
			'access_url' => 'url',
			'server_uri',
			'referer',
			'object_class_name' => 'class_name',
			'object_id',
			'target_class_name' => 'class_name',
			'target_object_id' => 'object_id',
			'access_time',
			'operation_time',
			'has_bors',
			'has_bors_url',
			'user_agent',
			'is_bot',
			'was_counted',
		);
	}

	function auto_targets()	{ return array('target' => 'target_class_name(target_object_id)'); }
	function auto_objects()	{ return array('user' => 'bors_user(user_id)'); }
}
