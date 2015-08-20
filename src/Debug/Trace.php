<?php

namespace B2\Debug;

class Trace
{
/*
00.000 [+0.0000] route begin /var/www/.....:75
00.001 [+0.0011]     body begin
00.002 [+0.0012]         attaches /var/www/.... ... ok
                     body ok
                 route ok
*/

	static $exec_trace_stack = array();
	static $last_ts  = 0;
	static $last_mem = 0;

	static function start($section)
	{
		if(!config('debug.execute_trace'))
			return;

		$now = microtime(true);
		$time = sprintf("%2.3f",  $now - $GLOBALS['stat']['start_microtime']);

		if(empty(self::$exec_trace_stack))
		{
			self::syslog('execute_trace_sections', str_repeat(80, '-'), false);
		}

		$delta_time = sprintf("%1.4f", $now - self::$last_ts);
		self::$last_ts = $now();

		$mem = memory_get_usage();
		$cur_mem = sprintf('%1.2f', $mem/1048576).'Mb';
		$delta_mem = sprintf('%1.2f', (self::$last_mem - $mem)/1048576);

		if($delta_mem > 0)
			$delta_mem = '+'.$delta_mem;

		self::$last_mem = $mem;

		self::$exec_trace_stack[] = array(
			'time' => $now,
			'mem' => $mem,
			'section' => $section,
		);

		$pad = str_repeat(count(self::$exec_trace_stack), "\t");
		self::syslog('execute_trace_sections', "$time [+$delta_time] ($cur_mem [$delta_mem])$pad$section begin", false);
	}

	static function stop($section)
	{
		if(!config('debug.execute_trace'))
			return;

		
	}
}
