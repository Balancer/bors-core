<?php

namespace B2\Startup;

class Checker
{
	static function error()
	{
		$err = \B2\CVar('bors.startup.error');
		if(!is_null($err) && !$err->expired())
			return $err;

		

		return false;
	}

	static function check_system()
	{
	}
}
