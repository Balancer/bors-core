<?php

class bors_modules_calend extends bors_module
{
	function body_data()
	{
		$now		= $this->args('now', time());
		$show_date	= $this->args('show_date', $now);

		$show_date_hr	= date('Y/m/d', $show_date);

		$year	= $this->args('year',  date('Y', $show_date));
		$month	= $this->args('month', date('m', $show_date));
		$day	= $this->args('day',   date('d', $show_date));

		$target_class_name	= $this->args('target_class_name');
		$target_count_class_name	= $this->args('target_count_class_name', $target_class_name);
		$calend_class_name	= $this->args('calend_class_name');
		$calend_mask		= $this->args('calend_mask');
		$where				= $this->args('where', array());
		$table_class		= $this->args('table_class', 'btab');
		$show_caption		= $this->args('show_caption', true);

		$time_field	= $this->args('time_field', 'create_time');
		$link_all_days = $this->args('link_all_days', false);

		$ajax				= $this->args('ajax', true);
		if($ajax)
		{
			template_jquery();
			template_js_include('/_bors3rdp/js/strftime-min.js');
		}

		$begin = strtotime("$year-$month-01 00:00:00");
		$month_end   = ($month == 12 ? strtotime(($year+1)."-01-01 00:00:00") : strtotime("$year-".($month+1)."-1 00:00:00"))-1;

		$first_weekday = date('N', $begin);
		$days_in_month = date('t', $begin);

		$calend = array();
		$days = array();

		for($i=1; $i<$first_weekday; $i++)
			$days[] = array(
				'class' => 'bc_disabled',
				'number' => date('d', $begin - 86400*($first_weekday-$i)),
				'count' => 0,
			);

//		config_set('mysql_trace_show', true);
		$counts = bors_count($target_count_class_name, array_merge($where, array(
			$time_field.' BETWEEN' => array($begin, $month_end),
			'group' => '*BYDAYS('.$time_field.')*',
		)));

		for($d = 1; $d <= $days_in_month; $d++)
		{
			$day_begin = strtotime("$year-$month-$d 00:00:00");
//			$day_end   = strtotime("$year-$month-$d 23:59:59");

//			$count = bors_count($target_count_class_name, array_merge($where, array(
//				'create_time BETWEEN' => array($day_begin, $day_end)
//			)));

			$count = intval(@$counts[sprintf('%04d-%02d-%02d', $year, $month, $d)]);

			$class = $count ? 'bc_normal' : 'bc_empty';
			if(date('d.m.Y', $show_date) == "$d.$month.$year")
				$class .= $class.' bc_now';

			$day_url = NULL;
			if($count || $link_all_days)
			{
				if($calend_mask)
					$day_url = strftime($calend_mask, $day_begin);
				else
					$day_url = bors_load($calend_class_name, $day_begin)->url();
			}

			$days[] = array(
				'number' => $d,
				'count' => $count,
				'url' => $day_url,
				'class' => $class,
			);

			if(count($days) == 7)
			{
				$calend[] = $days;
				$days = array();
			}
		}

		if($cnt = count($days))
		{
			for($i=1; $i<=7-$cnt; $i++)
				$days[] = array(
					'class' => 'bc_disabled',
					'number' => $i,
					'count' => 0
				);

			$calend[] = $days;
		}

		return compact('ajax', 'calend', 'calend_mask', 'day', 'month', 'now', 'show_caption', 'show_date', 'show_date_hr', 'table_class', 'year');
	}
}
