<?php

require_once('inc/strings.php');

class module_date_calend_month extends base_page
{
	function local_data()
	{
		$now		= $this->args('now', time());
		$show_date	= $this->args('show_date', $now);

		$year	= $this->args('year', strftime('%Y', $show_date));
		$month	= $this->args('month', strftime('%m', $show_date));
		$day	= $this->args('day', strftime('%d', $show_date));

		$time0 = intval(strtotime("$year-$month-1 00:00:00"));
		$days_in_month = date("t", $time0);
		$time9 = $time0 + 86400*$days_in_month;
		$wd1  = strftime("%u",$time0);

		$calend = array();
		$base_url = $this->args('base_url');
		if(preg_match('/^(strftime):(.+)$/', $base_url, $m))
		{
			$base_url = false;
			$time_func = $m[1];
			$format = $m[2];
			$begin_of_month = strtotime(date('Y-m-1', $time0));
		}

		$Ym = date('Y', $time0).'/'.date('m', $time0);

		if(!($list = $this->args('list')))
		{
			$list = array();
			$counts = array();

			$time_field = $this->args('class_time_field', 'create_time');

			if($this->args('class_name'))
				foreach(bors_find_all($this->args('class_name'), array(
					$this->args('class_name').".{$time_field} BETWEEN {$time0} AND {$time9}")
				) as $x)
					@$counts[date('j', $x->$time_field())]++;

			foreach($counts as $day => $count)
				$list[$day] = array(
					'url' => $base_url ? $base_url.$Ym.sprintf('/%02d/', $day) : $time_func($format, $begin_of_month + ($day-1)*86400), 
					'title' => $count .' '. sklon($count, $this->args('sklon')),
				);
		}

		$this_month = ($this->args('show_today', true)
			&& $year == strftime('%Y', $now) 
			&& $month == strftime('%m', $now));
		$this_day = strftime('%d', $now);

		$shown_days = 0;
		$show_empty = $this->args('show_empty', false);
		while($shown_days <= $days_in_month)
		{
			$week = array();
			for($wd=1; $wd<=7; $wd++)
			{
				if($shown_days == 0 && $wd == $wd1)
					$shown_days = 1;
					
				if($shown_days == 0 || $shown_days > $days_in_month)
					$week[] = array();
				else
				{
					$x = @$list[$shown_days];
					if($x)
					{
						$week[] = array(
							'number' => $shown_days,
							'url' => $x['url'],
							'title' => $x['title'],
							'now' => $this_month && $shown_days == $this_day,
						);
					}
					elseif($show_empty)
						$week[] = array(
							'number' => $shown_days,
							'url' => $base_url.$Ym.sprintf('/%02d/', $shown_days), 
							'title' => 'none',
							'now' => $this_month && $shown_days == $this_day,
						);
					else
						$week[] = array('number'=> $shown_days);

					$shown_days++;
				}

			}

			$calend[] = $week;
		}

		return array(
			'calend' => $calend, 
			'table_class' => $this->args('table', 'btab'),
			'now' => $now,
			'show_date' => $show_date,
			'show_caption' => $this->args('show_caption'),
		);
	}
}
