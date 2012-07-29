<?php

class blib_month
{
	static function name($month_number)
	{
		bors_function_include('time/month_name');
		return month_name($month_number);
	}

	static function name_g($month_number)
	{
		bors_function_include('time/month_name_rp');
		return month_name_rp($month_number);
	}

	static function begin($year, $month)
	{
		return strtotime("$year-$month-01 00:00:00");
	}

	static function end($year, $month)
	{
		$next_month = $month+1;
		if($next_month == 13)
			$year++;

		return strtotime("$year-$next_month-01 00:00:00") - 1;
	}
}
