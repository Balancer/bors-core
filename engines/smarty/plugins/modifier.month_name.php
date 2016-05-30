<?php

function smarty_modifier_month_name($month)
{
	require_once BORS_CORE.'/inc/datetime.php';
   	return month_name($month);
}
