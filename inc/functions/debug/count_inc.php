<?php

$GLOBALS['bors_debug_counts'] = array();

function debug_count_inc($category, $inc = 1)
{
	if(empty($GLOBALS['bors_debug_counts'][$category]))
		$GLOBALS['bors_debug_counts'][$category] = 0;

	$GLOBALS['bors_debug_counts'][$category] += $inc;
}
