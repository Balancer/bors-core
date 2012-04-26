<?php

function date_format_mysqltime($time, $escape = true)
{
	if(!$time)
		return NULL;

	return $escape ? strftime('\'%Y-%m-%d %H:%M:%S\'', $time) : strftime('%Y-%m-%d %H:%M:%S', $time);
}
