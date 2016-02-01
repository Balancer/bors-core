<?php

class bors_lib_date
{
	static function interval($begin_date, $end_date, $show_year = true)
	{
		bors_function_include('time/month_name_rp');

		if(!$begin_date)
			return '';

		$d1 = intval(strftime('%d', $begin_date));
		$m1 = intval(strftime('%m', $begin_date));
		$d2 = intval(strftime('%d', $end_date));
		$m2 = intval(strftime('%m', $end_date));

		if($show_year)
		{
			$y1 = strftime('%Y', $begin_date);
			$y2 = strftime('%Y', $end_date);

			if($y1 != $y2)
				return "{$d1}.{$m1}.{$y1} — {$d2}.{$m2}.{$y2}";
		}
		else
			$y1 = "";

		if($m1 != $m2)
			return bors_lower("{$d1} ".month_name_rp($m1)." — {$d2} ".month_name_rp($m2)." {$y1}");

		if($d1 != $d2)
			return "{$d1} — {$d2} ".bors_lower(month_name_rp($m1))." {$y1}";

		return "{$d1} ".bors_lower(month_name_rp($m1))." {$y1}";
	}

	// Используется на http://admin.aviaport.wrk.ru/infrastructure/airports/activities/
	static function interval_month($begin_date, $end_date, $show_year = true)
	{
		if(!$begin_date)
			return '';

		$m1 = intval(strftime('%m', $begin_date));
		$m2 = intval(strftime('%m', $end_date));

		if($show_year)
		{
			$y1 = strftime('%Y', $begin_date);
			$y2 = strftime('%Y', $end_date);

			if($y1 != $y2)
				return bors_lower(blib_month::name($m1).' '.$y1.' г. — '.blib_month($m2).' '.$y2.' г.');

			$y1 = " {$y1} г.";
		}
		else
			$y1 = "";

		if($m1 != $m2)
			return bors_lower(blib_month::name($m1)." — ".blib_month::name($m2).$y1);

		return bors_lower(blib_month::name($m1)).$y1;
	}

	static function part($date, $int = false, $on_empty_text = '', $short=false, $rp=false)
	{
		bors_function_include('time/part_date');
		return part_date($date, $int, $on_empty_text, $short);
	}

	static function text($timestamp, $show_year = true)
	{
		bors_function_include('date/text_date');
		return text_date($timestamp, $show_year);
	}
}
