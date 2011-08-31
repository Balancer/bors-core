<?php

class bors_debug_log extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function db_name() { return config('bors_logs_db'); }
	function table_name() { return 'bors_debug_log'; }
	function table_fields()
	{
		return array(
			'id',
			'create_time' => 'UNIX_TIMESTAMP(`create_time`)',
			'title',
			'category',
			'level',
			'trace',
			'owner_id',
			'request_uri',
			'get_vars' => 'get_data',
			'referer',
			'remote_addr',
			'server_data',
		);
	}
}
