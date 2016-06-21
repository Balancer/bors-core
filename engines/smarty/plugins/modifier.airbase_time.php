<?php

function smarty_modifier_airbase_time($time)
{
	require_once BORS_CORE.'/inc/datetime.php';
   	return airbase_time($time);
}
