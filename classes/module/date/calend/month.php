<?php

class module_date_calend_month extends base_page
{
	function local_template_data_set()
	{
		$year	= $this->args('year', strftime('%Y'));
		$month	= $this->args('month', strftime('%m'));
		$day	= $this->args('day', strftime('%d'));
		$now	= $this->args('now', time());

		$time0 = intval(strtotime("$year-$month-1 00:00:00"));
		$days_in_month = date("t", $time0);
		$wd1  = strftime("%u",$time0);

		$calend = array();

		if(!($list = $this->args('list')))
		{
			$list = array();
			$counts = array();
			foreach(objects_array($this->args('class_name'), array("create_time BETWEEN {$time0} AND {$time9}")) as $x)
				@$counts[date('j', $x->create_time())]++;
			foreach($counts as $day => $count)
				$list[$day] = array(
					'url' => $this->args('base_url').date('Y/m/d/'), 
					'title' => $count .' '. sklon($count, $this->args('sklon')),
				);
		}

		$this_month = $year == strftime('%Y', $now) && $month == strftime('%m', $now);
		$today = strftime('%d', $now);

		$shown_days = 0;
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
							'now' => $this_month && $shown_days == $today,
						);
					}
					else
						$week[] = array('number'=>$shown_days);

					$shown_days++;
				}

			}

			$calend[] = $week;
		}

		return array('calend' => $calend, 'table_class' => $this->args('table', 'btab'));
	}
}
