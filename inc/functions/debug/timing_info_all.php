<?php

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
