<?php

function debug_timing_stop($category)
{
	global $bors_debug_timing;
	$current = &$bors_debug_timing[$category];

	if(empty($current['start']))
	{
//		debug_hidden_log('__debug_error', ec("Вызов неактивированной функции debug_timing_stop('$category')."));
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
