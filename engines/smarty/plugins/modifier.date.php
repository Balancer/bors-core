<?php

function smarty_modifier_date($time, $mask)
{
	if($time == 0)
		$time = $GLOBALS['now'];
	
	if(!preg_match("!^\d+$!", $time))
		return $time;

	return date($mask, $time);
}
