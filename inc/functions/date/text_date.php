<?php

require_once BORS_CORE.'/inc/functions/time/month_name_rp.php';

function text_date($date, $show_year = true)
{
	return date('j', $date).' '.bors_lower(month_name_rp(date('n', $date)))
		.($show_year ? ' '.date('Y', $date) : '');
}
