<?php

function full_time($time)
{
	return strftime("%d.%m.%Y %H:%M",$time);
}

function short_time($time)
{
	if(!$time)
		return '';

	global $now;
	$time = intval($time);

	if(abs($now - $time) < 86400 && strftime("%d", $time) == strftime("%d", $now))
		return strftime("%H:%M", $time);
	else
		return strftime("%d.%m.%Y", $time);
}

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

function smart_time($time, $human_readable = true)
{
	global $now;
	if(is_today($time))
		return ec(strftime("сегодня, %H:%M",$time));
	
	if($now - $time < 2*86400 && strftime("%d",$time) == strftime("%d",$now-86400))
		return ec("вчера, ").strftime("%H:%M",$time);
	
	return $human_readable ? full_hdate($time) : strftime("%d.%m.%Y",$time);
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
	return date('j', $date).' '.strtolower(month_name_rp(date('n', $date))).' '.date('Y', $date);
}

function make_input_time($field_name, &$data)
{
	foreach(explode(' ', 'year month day hour min sec') as $key)
	{
		$name = $field_name.'_'.$key;
		$$key = intval(@$data[$name]);
		unset($data[$name]);
	}
	
	if(!$day)
		$day = 1;

	if(!$month)
		$month = 1;

	if(!$year)
		$year = date('Y', $GLOBALS['now']);

	return $data[$field_name] = strtotime("{$year}-{$month}-{$day} $hour:$min:$sec");
}

function full_hdate($date, $show_year = true)
{
	if(!$date)
		$date = time();

	return date('j', $date).' '.strtolower(month_name_rp(date('n', $date))).($show_year ? ec(strftime(' %Y года', $date)) : '');
}

function date_format_mysqltime($time) { return $time ? strftime('\'%Y-%m-%d %H:%M:%S\'', $time) : NULL; }
function date_format_mysql($time) { return $time ? strftime('\'%Y-%m-%d\'', $time) : NULL; }

function date_day_begin($time) { return strtotime(date('Y-m-d', $time)); }

function part_date($date)
{
	if(is_numeric($date))
		list($year, $month, $day) = explode('-', date('Y-m-d', $date));
	else
		list($year, $month, $day) = explode('-', $date);

	if(!$year)
		return '';

	if(!$month)
		return $year.ec(' г.');
	if(!$day)
		return month_name($month).' '.$year.ec(' г.');

	return $day.' '.month_name_rp($month).' '.$year.ec(' г.');
}

function smart_interval($interval, $parts = 2)
{
	$res = array();
	$res[] = ($x = $interval % 60) ? $x.' секунд'.sklon($x,'а,ы,') : '';
	$interval = intval($interval/60);
	$res[] = ($x = $interval % 60) ? $x.' минут'.sklon($x,'а,ы,') : '';
	$interval = intval($interval/60);
	$res[] = ($x = $interval % 24) ? $x.' час'.sklon($x,',а,ов') : '';
	$interval = intval($interval/24);

	$res[] = ($x = $interval % 365) ? $x.' '.sklon($x,'день,дня,дней') : '';
	$interval = intval($interval/365);

	$res[] = ($x = $interval) ? $x.' '.sklon($x,'год,года,лет') : '';

	$res = array_reverse($res);

	for($i=0; $i<count($res); $i++)
		if(!empty($res[$i]))
			break;
	
	return join(' ', array_slice($res, $i, $parts));
}
