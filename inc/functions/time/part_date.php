<?php

function part_date($date, $int = false, $on_empty_text = '', $short=false, $rp=false)
{
	//TODO: хак для http://www.aviaport.ru/job/resumes/747/ && #1248
	if(is_numeric($date) && $date > 99999999)
		return month_name(date('m', $date)).' '.date('Y', $date).ec(' г.');

	$year = $month = $day = 0;
	if($int)
	{
		$year = substr($date, 0, 4);
		$date = substr($date, 4);
		if($date != 0)
		{
			$month = substr($date, 0, 2);
			$date = substr($date, 2);
		}
		if($date != 0)
		{
			$day = substr($date, 0, 2);
			$date = substr($date, 2);
		}
	}
	elseif(is_numeric($date))
		list($year, $month, $day) = explode('-', date('Y-m-d', $date));
	else
		list($year, $month, $day) = explode('-', $date);

	if($year == 0)
		return $on_empty_text;

	if($month == 0)
		return $year.ec(' г.');

	if($day == 0)
	{
		if($rp)
		{
			bors_function_include('time/month_name_rp');
			return bors_lower(month_name_rp($month)).' '.$year.ec(' г.');
		}

		bors_function_include('time/month_name');
		return month_name($month).' '.$year.ec(' г.');
	}

	if($short)
		return sprintf("%02d.%02d.%04d", $day, $month, $year);

	bors_function_include('time/month_name_rp');
	return $day.' '.bors_lower(month_name_rp($month)).' '.$year.ec(' г.');
}
