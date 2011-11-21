<?php

function date_format_mysqltime($time)
{
	return $time ? strftime('\'%Y-%m-%d %H:%M:%S\'', $time) : NULL;
}
