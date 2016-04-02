<?php

require_once BORS_CORE.'/inc/functions/time/smart_date.php';

function smart_time($time, $human_readable = true, $def='', $always_show_time = true)
{
	return smart_date($time, $human_readable, $def, $always_show_time);
}
