<?php

require_once BORS_CORE.'/inc/functions/debug/count_inc.php';

function debug_count_info_all()
{
	$result = "";

	global $bors_debug_counts;
	if($bors_debug_counts)
	{
		ksort($bors_debug_counts);
		foreach($bors_debug_counts as $section => $count)
			$result .= $section.": {$count}\n";
	}

	return $result;
}
