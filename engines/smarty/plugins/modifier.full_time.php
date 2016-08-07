<?php

function smarty_modifier_full_time($time)
{
	require_once __DIR__.'/../../../inc/datetime.php';
   	return full_time($time);
}
