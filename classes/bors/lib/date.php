<?php

class bors_lib_date
{
	static function date_interval($begin_date, $end_date, $show_year = true)
	{
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
				return "$d1.$m1.$y1&nbsp;&mdash;&nbsp;$d2.$m2.$y2";
		}
		else
			$y1 = "";

		if($m1 != $m2)
			return bors_lower("$d1&nbsp;".month_name_rp($m1)."&nbsp;&mdash; $d2&nbsp;".month_name_rp($m2)." $y1");

		if($d1 != $d2)
			return "$d1&nbsp;&mdash;&nbsp;$d2 ".bors_lower(month_name_rp($m1))." $y1";

		return "$d1 ".bors_lower(month_name_rp($m1))." $y1";
	}
}
