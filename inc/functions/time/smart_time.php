<?php

bors_function_include('time/smart_date');

function smart_time($time, $human_readable = true, $def='', $always_show_time = true)
{
	return smart_date($time, $human_readable, $def, $always_show_time);
}
