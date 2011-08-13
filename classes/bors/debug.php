<?php

class bors_debug
{
	static function log($category, $message = NULL, $level = 'info', $trace = true)
	{
		static $enter = false;
		if($enter)
			return;

		$enter = true;

		bors_new('bors_debug_log', array(
			'create_time' => time(),
			'title' => $message,
			'category' => $category,
			'level' => $level,
			'trace' => serialize(array_slice(debug_backtrace(), 0, 100)),
			'owner_id' => bors()->user_id(),
			'request_uri' => bors()->request()->url(),
			'get_vars' => json_encode(@$_GET),
			'referer' => bors()->request()->referer(),
			'remote_addr' => @$_SERVER['REMOTE_ADDR'],
			'server_data' => strlen(serialize($_SERVER)),
		));

		$enter = false;
	}
}

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
