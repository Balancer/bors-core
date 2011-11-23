<?php

bors_function_include('time/month_name_rp');

function text_date($date)
{
	return date('j', $date).' '.bors_lower(month_name_rp(date('n', $date))).' '.date('Y', $date);
}
