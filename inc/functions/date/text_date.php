<?php

bors_function_include('time/month_name_rp');

function text_date($date, $show_year = true)
{
	return date('j', $date).' '.bors_lower(month_name_rp(date('n', $date)))
		.($show_year ? ' '.date('Y', $date) : '');
}
