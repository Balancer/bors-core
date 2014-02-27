<?php

class bors_debug
{
	static function syslog($type, $message, $trace = true, $args = array())
	{
		bors_function_include('debug/hidden_log');
		return debug_hidden_log($type, $message, $trace, $args);
	}

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

	static function timing_start($section)
	{
		bors_function_include('debug/timing_start');
		debug_timing_start($section);
	}

	static function timing_stop($section)
	{
		bors_function_include('debug/timing_stop');
		debug_timing_stop($section);
	}

	static function trace($skip = 0, $html = NULL, $level = -1, $traceArr = NULL)
	{
		bors_function_include('debug/trace');
		return debug_trace($skip = 0, $html = NULL, $level = -1, $traceArr = NULL);
	}

	static function warning($message)
	{
		
	}
}
