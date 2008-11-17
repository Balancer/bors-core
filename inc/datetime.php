<?php

function full_time($time)
{
	return strftime("%d.%m.%Y %H:%M",$time);
}

function short_time($time)
{
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

function news_short_time($time)
{
	if(is_today($time))
		return strftime("%H:%M", $time);
	
	if($GLOBALS['now'] - $time < 2*86400 && strftime("%d",$time) == strftime("%d", $GLOBALS['now']-86400))
		return ec("Вчера");
		
	return strftime("%d.%m.%Y", $time);
}

$GLOBALS['month_names'] = explode(' ', ec('Январь Февраль Март Апрель Май Июнь Июль Август Сентябрь Октябрь Ноябрь Декабрь'));
$GLOBALS['month_names_rp'] = explode(' ', ec('Января Феврал Марта Апреля Мая Июня Июля Августа Сентября Октября Ноября Декабря'));

function month_name($m) { return $GLOBALS['month_names'][$m-1]; }
function month_name_rp($m) { return $GLOBALS['month_names_rp'][$m-1]; }

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
	return date('j', $date).' '.strtolower(month_name_rp(date('n', $date))).($show_year ? ec(strftime(' %Y года', $date)) : '');
}

function date_format_mysqltime($time) { return $time ? strftime('\'%Y-%m-%d %H:%M:%S\'', $time) : NULL; }
function date_format_mysql($time) { return $time ? strftime('\'%Y-%m-%d\'', $time) : NULL; }
