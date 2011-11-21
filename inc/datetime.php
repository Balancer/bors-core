<?php

require_once('inc/strings.php');

function full_time($time) { return $time ? strftime("%d.%m.%Y %H:%M",$time) : '-'; }

function short_time($time, $def = '') { return bors_lib_time::short($time, $def); }

function is_today($time)
{
	global $now;
	if($now - $time < 86400 && strftime("%d", $time) == strftime("%d", $now))
		return true;

	//FIXME: разобраться, wtf?
	if(preg_match("!\d{4}/\d{1,2}/\d{1,2}/$!", @$GLOBALS['main_uri']))
		return true;

	return false;
}

function news_time($time)
{
	global $now;

	if(is_today($time))
		return strftime("%H:%M",$time);

	if($now - $time < 2*86400 && strftime("%d",$time) == strftime("%d", $now-86400))
		return ec("Вчера, ").strftime("%H:%M",$time);

	return strftime("%d.%m.%Y %H:%M",$time);
}

function airbase_time($time)
{
	global $now;
	if(is_today($time))
		return ec(strftime("сегодня, %H:%M",$time));

	if($now - $time < 2*86400 && strftime("%d",$time) == strftime("%d",$now-86400))
		return ec("вчера, ").strftime("%H:%M",$time);

	return strftime("%Y-%m-%d",$time);
}

function smart_date($time, $human_readable = true, $def='', $always_show_time = false)
{
	if(!$time)
		return $def;

	global $now;
	if(is_today($time))
		return ec(strftime("сегодня,&nbsp;%H:%M",$time));

	if($now - $time < 2*86400 && strftime("%d",$time) == strftime("%d",$now-86400))
		return ec("вчера,&nbsp;").strftime("%H:%M",$time);

	$hhmm = $always_show_time ? date(' H:i', $time) : '';

	return ($human_readable ? full_hdate($time) : strftime("%d.%m.%Y", $time)).$hhmm;
}

function smart_time($time, $human_readable = true, $def='', $always_show_time = true)
{
	return smart_date($time, $human_readable, $def, $always_show_time);
}

function news_short_time($time)
{
	if(is_today($time))
		return strftime("%H:%M", $time);

	if($GLOBALS['now'] - $time < 2*86400 && strftime("%d",$time) == strftime("%d", $GLOBALS['now']-86400))
		return ec("Вчера");

	return strftime("%d.%m.%Y", $time);
}

$GLOBALS['month_names'] = explode(' ', 'Январь Февраль Март Апрель Май Июнь Июль Август Сентябрь Октябрь Ноябрь Декабрь');
$GLOBALS['month_names_rp'] = explode(' ', 'Января Февраля Марта Апреля Мая Июня Июля Августа Сентября Октября Ноября Декабря');

function month_name($m) { return ec($GLOBALS['month_names'][$m-1]); }
function month_name_rp($m) { return ec($GLOBALS['month_names_rp'][$m-1]); }

$GLOBALS['day_names'] = explode(' ', 'Понедельник Вторник Среда Четверг Пятница Суббота Воскресенье');

function week_day_name($day_num) { return ec($GLOBALS['day_names'][$day_num-1]); }

function text_date($date)
{
	return date('j', $date).' '.bors_lower(month_name_rp(date('n', $date))).' '.date('Y', $date);
}

function full_hdate($date, $show_year = true)
{
	if(!$date)
		$date = time();

	return date('j', $date).' '.bors_lower(month_name_rp(date('n', $date))).($show_year ? ec(strftime(' %Y года', $date)) : '');
}

bors_function_include('time/date_format_mysqltime');

function date_format_mysql($time, $quoted=true) { $q=$quoted?"'":''; return $time ? $q.date('Y-m-d', $time).$q : NULL; }

function date_day_begin($time = 0) { return strtotime(date('Y-m-d', $time ? $time : time())); }
function date_day_next($time)   { return strtotime(date('Y-m-d', $time).' +1 day');  }
function date_month_next($time) { return strtotime(date('Y-m-d', $time).' +1 month');}
function date_year_next($time)  { return strtotime(date('Y-m-d', $time).' +1 year'); }

function date_today($time = 0)     { return strtotime(date('Y-m-d', $time ? $time : time())); }
function date_yesterday($time = 0) { return strtotime(date('Y-m-d', $time ? $time : time()).' -1 day'); }
function date_tomorrow ($time = 0) { return strtotime(date('Y-m-d', $time ? $time : time()).' +1 day'); }

function part_date($date, $int = false, $on_empty_text = '')
{
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
		return $year.ec(' г.');
	if($day == 0)
		return month_name($month).' '.$year.ec(' г.');

	return $day.' '.month_name_rp($month).' '.$year.ec(' г.');
}

function smart_interval($interval, $parts = 2)
{
	$res = array();
	$res[] = ($x = $interval % 60) ? $x.ec(' секунд').sklon($x,ec('а,ы,')) : '';
	$interval = intval($interval/60);
	$res[] = ($x = $interval % 60) ? $x.ec(' минут').sklon($x,ec('а,ы,')) : '';
	$interval = intval($interval/60);
	$res[] = ($x = $interval % 24) ? $x.ec(' час').sklon($x,ec(',а,ов')) : '';
	$interval = intval($interval/24);

	$res[] = ($x = $interval % 365) ? $x.' '.sklon($x,ec('день,дня,дней')) : '';
	$interval = intval($interval/365);

	$res[] = ($x = $interval) ? $x.' '.sklon($x, ec('год,года,лет')) : '';

	$res = array_reverse($res);

	for($i=0; $i<count($res); $i++)
		if(!empty($res[$i]))
			break;

	return join(' ', array_slice($res, $i, $parts));
}

function short_interval($ss)
{
	$mm = floor($ss/60);
	$ss = sprintf('%02d', $ss % 60);
	if(!$mm)
		return intval($ss).ec(' сек.');

	$hh = floor($mm/60);
	$mm = sprintf('%02d', $mm % 60);
	if(!$hh)
		return intval($mm).':'.$ss;

	return intval($hh).':'.$hh.':'.$ss;
}

function time_local_to_gmt($time = 0)
{
	if(!$time)
		$time = time();

	return mktime( gmdate("H", $time), gmdate("i", $time), gmdate("s", $time), gmdate("m", $time), gmdate("d", $time), gmdate("Y", $time));
}
