<?php

function smarty_modifier_fuzzy_date($date)
{
	if(!preg_match('/^(\d{4})(\d\d)(\d\d)$/', $date, $m))
	{
		bors_debug::syslog('date_error', "Unknown fuzzy date format: ".$date);
		return $date;
	}

	return bors_lib_date::part($date, true);
}
