<?php

bors_function_include('time/month_name_rp');

function full_hdate($date, $show_year = true)
{
	if(!$date)
		$date = time();

	return date('j', $date).' '.bors_lower(month_name_rp(date('n', $date))).($show_year ? ec(strftime(' %Y года', $date)) : '');
}
