<?php

bors_function_include('date/is_today');

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

	bors_function_include('date/full_hdate');
	return ($human_readable ? full_hdate($time) : strftime("%d.%m.%Y", $time)).$hhmm;
}
