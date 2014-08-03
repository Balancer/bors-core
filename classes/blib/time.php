<?php

class blib_time
{
	static function short($time, $def = '')
	{
		return bors_lib_time::short($time, $def);
	}

	static function day_begin($timestamp = NULL)
	{
		return strtotime(date('Y-m-d', is_null($timestamp) ? time() : $timestamp));
	}
}
