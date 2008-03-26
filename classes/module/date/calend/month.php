<?php

class module_date_calend_month extends base_page
{
	function class_file() { return __FILE__; }

	function data_providers()
	{
		$year	= $this->args('year', strftime('%Y'));
		$month	= $this->args('month', strftime('%m'));
		$day	= $this->args('day', strftime('%d'));

		$time0 = intval(strtotime("$year-$month-1 00:00:00"));
		
		$mm = $month;
		$yy = $year;
		
		if($mm<12)
			$mm = $mm + 1;
		else
		{
			$mm=1;
			$yy++;
		}
		
		$time9 = intval(strtotime("$yy-$mm-1 00:00:00"))-1;

		$days_in_month = strftime("%d",$time9);
		$wd1  = strftime("%u",$time0);

		$calend = array();

		$list = $this->args('list');

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
						);
					}
					else
						$week[] = array('number'=>$shown_days);

					$shown_days++;
				}

			}

			$calend[] = $week;
		}

		return array('calend' => $calend, 'table_class' => $this->args('table'));
	}
}
