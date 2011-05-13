<?php

class bors_modules_calend extends bors_module
{
	function body_data()
	{
		$now		= $this->args('now', time());
		$show_date	= $this->args('show_date', $now);

		$year	= $this->args('year',  date('Y', $show_date));
		$month	= $this->args('month', date('m', $show_date));
		$day	= $this->args('day',   date('d', $show_date));

		$target_class_name	= $this->args('target_class_name');
		$calend_class_name	= $this->args('calend_class_name');
		$calend_mask		= $this->args('calend_mask');
		$where				= $this->args('where');
		$table_class		= $this->args('table_class', 'btab');
		$show_caption		= $this->args('show_caption', true);

		$ajax_calend		= $this->args('ajax_calend', true);

		$begin = strtotime("$year-$month-1 00:00:00");

		$first_weekday = date('N', $begin);
		$days_in_month = date('t', $begin);

		$calend = array();
		$days = array();

		for($i=1; $i<$first_weekday; $i++)
			$days[] = array('type' => 'disabled', 'number' => date('d', $begin - 86400*($first_weekday-$i)), 'count' => 0);

		for($d = 1; $d <= $days_in_month; $d++)
		{
			$day_begin = strtotime("$y-$m-$d 00:00:00");
			$day_end   = strtotime("$y-$m-$d 23:59:59");
			$count = bors_count($target_class_name, array_merge($where, array('create_time BETWEEN' => $day_begin, $day_end)));
			$days[] = array(
				'type' => $count ? 'normal' : 'empty',
				'now' => $d == $day,
				'number' => $day,
				'count' => $count,
				'url' => $calend_mask ? date($calend_mask, $day_begin) : bors_load($calend_class_name, $day_begin)->url();
			);

			if(count($days) == 7)
			{
				$calend[] = $days;
				$days = array();
			}
		}

		if($cnt = count($days))
		{
			$i = 1;
			while($cnt--)
				$days[] = array('type' => 'disabled', 'number' => $i++, 'count' => 0);

			$calend[] = $days;
		}

		return compact('calend', 'month', 'now', 'show_caption', 'show_date', 'table_class', 'year');
	}
}
