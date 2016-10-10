<?php

namespace B2;

class Cfg
{
	static function set($key, $value)
	{
		return $GLOBALS['cms']['config'][$key] = $value;
	}

	// Не максировать через @!
	static function get($key, $def = NULL)
	{
		if(array_key_exists($key, $GLOBALS['cms']['config']))
			return  $GLOBALS['cms']['config'][$key];

		return $def;
	}
}
