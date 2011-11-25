<?php

$GLOBALS['bors_debug_counts'] = array();
function debug_count_inc($category, $inc = 1) { @$GLOBALS['bors_debug_counts'][$category] += $inc; }
function debug_count($category) { return @$GLOBALS['bors_debug_counts'][$category]; }

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
