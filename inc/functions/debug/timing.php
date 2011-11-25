<?php

$GLOBALS['bors_debug_timing'] = array();
function debug_timing_start($category)
{
	global $bors_debug_timing;
	if(empty($bors_debug_timing[$category]))
		$bors_debug_timing[$category] = array('start' => NULL, 'calls'=>0, 'total'=>0, 'mem_total' => 0);

	$current = &$bors_debug_timing[$category];

	if($current['start'])
	{
		//TODO: need best method
//		debug_hidden_log('__debug_error', ec("Вторичный вызов незавершённой функции debug_timing_start('$category')."));
		return;
	}

	$current['start'] = microtime(true);
	$current['mem'] = memory_get_usage();
}

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

function debug_timing_info_all()
{
	$time = microtime(true) - $GLOBALS['stat']['start_microtime'];

	global $bors_debug_timing;
	$result = "";
	if($bors_debug_timing)
	{
		ksort($bors_debug_timing);
		foreach($bors_debug_timing as $section => $data)
			$result .= $section.": ".sprintf('%.4f', floatval(@$data['total'])).'sec ['.intval(@$data['calls'])." calls, ".sprintf('%.2f', $data['total']/$time * 100)."%, {$data['mem_total']}]\n";
	}

	return $result;
}
