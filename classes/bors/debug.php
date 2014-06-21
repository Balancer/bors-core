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
		global $bors_debug_timing;
		if(empty($bors_debug_timing[$section]))
			$bors_debug_timing[$section] = array('start' => NULL, 'calls'=>0, 'total'=>0, 'mem_total' => 0);

		$current = &$bors_debug_timing[$section];

		if($current['start'])
		{
			//TODO: need best method
//			debug_hidden_log('__debug_error', ec("Вторичный вызов незавершённой функции debug_timing_start('$section')."));
			return;
		}

		$current['start'] = microtime(true);
		$current['mem'] = memory_get_usage();
	}

	static function timing_stop($section)
	{
		global $bors_debug_timing;
		$current = &$bors_debug_timing[$section];

		if(empty($current['start']))
		{
//			debug_hidden_log('__debug_error', ec("Вызов неактивированной функции debug_timing_stop('$section')."));
			return;
		}

		$mem = memory_get_usage() - $current['mem'];
		$time = microtime(true) - $current['start'];

		$current['start'] = NULL;
		$current['mem'] = NULL;
		$current['calls']++;
		$current['total'] += $time;
		$current['mem_total'] += $mem;
	}

	static function trace($skip = 0, $html = NULL, $level = -1, $traceArr = NULL)
	{
		bors_function_include('debug/trace');
		return debug_trace($skip = 0, $html = NULL, $level = -1, $traceArr = NULL);
	}

	static function warning($message)
	{

	}

	//TODO: убрать аналог из bors_global
	static function memory_usage() { return round(memory_get_usage()/1048576)."/".round(memory_get_peak_usage()/1048576)."MB"; }

	static function memory_usage_ping()
	{
		static $prev_usage = 0, $prev_peak_usage = 0;
		static $mb = 1048576;
		$cur_usage = memory_get_usage();
		$cur_peak_usage = memory_get_peak_usage();

		$usage_delta = round(($cur_usage - $prev_usage) / $mb, 2);
		if($usage_delta > 0)
			$usage_delta = "+$usage_delta";

		$peak_usage_delta = round(($cur_peak_usage - $prev_peak_usage) / $mb, 2);
		if($peak_usage_delta > 0)
			$peak_usage_delta = "+$peak_usage_delta";

		$report = round($cur_usage/$mb, 2)."({$usage_delta})/".round($cur_peak_usage/$mb, 2)."({$peak_usage_delta}) MB";

		$prev_usage = $cur_usage;
		$prev_peak_usage = $cur_peak_usage;

		return $report;
	}
}
