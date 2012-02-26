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
