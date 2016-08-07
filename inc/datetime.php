<?php

require_once BORS_CORE.'/inc/strings.php';
require_once BORS_CORE.'/inc/functions/date/format_mysql.php';
require_once BORS_CORE.'/inc/functions/date/full_hdate.php';
require_once BORS_CORE.'/inc/functions/date/is_today.php';
require_once BORS_CORE.'/inc/functions/time/part_date.php';
require_once BORS_CORE.'/inc/functions/date/today.php';
require_once BORS_CORE.'/inc/functions/date/text_date.php';
require_once BORS_CORE.'/inc/functions/date/yesterday.php';
require_once BORS_CORE.'/inc/functions/time/date_format_mysqltime.php';
require_once BORS_CORE.'/inc/functions/time/month_name.php';
require_once BORS_CORE.'/inc/functions/time/month_name_rp.php';
require_once BORS_CORE.'/inc/functions/time/smart_time.php';

function full_time($time)
{
	$d = date("d.m.Y", $time);
//	if($d == '01.04.2014')
//		$d = '60.02.2014';

	return $time ? $d.strftime(" %H:%M", $time) : '-';
}

function short_time($time, $def = '') { return bors_lib_time::short($time, $def); }

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

function news_short_time($time)
{
	if(is_today($time))
		return strftime("%H:%M", $time);

	if($GLOBALS['now'] - $time < 2*86400 && strftime("%d",$time) == strftime("%d", $GLOBALS['now']-86400))
		return ec("Вчера");

	return strftime("%d.%m.%Y", $time);
}

$GLOBALS['day_names'] = explode(' ', 'Понедельник Вторник Среда Четверг Пятница Суббота Воскресенье');

function week_day_name($day_num) { return ec($GLOBALS['day_names'][$day_num-1]); }


function date_day_begin($time = 0) { return blib_time::day_begin($time); }
function date_day_next($time)   { return strtotime(date('Y-m-d', $time).' +1 day');  }
function date_month_next($time) { return strtotime(date('Y-m-d', $time).' +1 month');}
function date_year_next($time)  { return strtotime(date('Y-m-d', $time).' +1 year'); }

function date_tomorrow ($time = 0) { return strtotime(date('Y-m-d', $time ? $time : time()).' +1 day'); }

function smart_interval($interval, $parts = 2) { return bors_lib_time::smart_interval($interval, $parts); }

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
