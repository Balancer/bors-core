<?php

require_once('inc/strings.php');

function full_time($time)
{
	$d = date("d.m.Y", $time);
//	if($d == '01.04.2014')
//		$d = '60.02.2014';

	return $time ? $d.strftime(" %H:%M", $time) : '-';
}

function short_time($time, $def = '') { return bors_lib_time::short($time, $def); }

bors_function_include('date/is_today');

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
		return ec('сегодня').strftime(", %H:%M", $time);

	if($now - $time < 2*86400 && strftime("%d", $time) == strftime("%d", $now-86400))
		return ec('вчера').strftime(", %H:%M", $time);

	return strftime("%Y-%m-%d",$time);
}

bors_function_include('time/smart_time');

function news_short_time($time)
{
	if(is_today($time))
		return strftime("%H:%M", $time);

	if($GLOBALS['now'] - $time < 2*86400 && strftime("%d",$time) == strftime("%d", $GLOBALS['now']-86400))
		return ec("Вчера");

	return strftime("%d.%m.%Y", $time);
}

bors_function_include('time/month_name');
bors_function_include('time/month_name_rp');
bors_function_include('date/text_date');

$GLOBALS['day_names'] = explode(' ', 'Понедельник Вторник Среда Четверг Пятница Суббота Воскресенье');

function week_day_name($day_num) { return ec($GLOBALS['day_names'][$day_num-1]); }

bors_function_include('date/full_hdate');
bors_function_include('time/date_format_mysqltime');
bors_function_include('date/format_mysql');

function date_day_begin($time = 0) { return strtotime(date('Y-m-d', $time ? $time : time())); }
function date_day_next($time)   { return strtotime(date('Y-m-d', $time).' +1 day');  }
function date_month_next($time) { return strtotime(date('Y-m-d', $time).' +1 month');}
function date_year_next($time)  { return strtotime(date('Y-m-d', $time).' +1 year'); }

bors_function_include('date/today');
bors_function_include('date/yesterday');
function date_tomorrow ($time = 0) { return strtotime(date('Y-m-d', $time ? $time : time()).' +1 day'); }

bors_function_include('time/part_date');

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
