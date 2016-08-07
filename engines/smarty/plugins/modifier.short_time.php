<?php

function smarty_modifier_short_time($time, $def = '')
{
	require_once BORS_CORE.'/inc/datetime.php';
   	return short_time($time, $def);
}
