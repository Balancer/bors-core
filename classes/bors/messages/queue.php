<?php

class bors_messages_queue extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function db_name() { return 'BORS'; }
	function table_name() { return 'bors_actions_queue'; }
	function table_fields()
	{
		return array(
			'id',
			'uid',
			'recipient_class_name',
			'recipient_object_id',
			'target_class_name',
			'target_object_id',
			'create_time',
			'expire_time',
			'message',
		);
	}
}
