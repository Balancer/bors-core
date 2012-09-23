<?php

class bors
{
	static function init()
	{
		if(!defined('BORS_CORE'))
			define('BORS_CORE', dirname(dirname(__FILE__)));

		require_once(BORS_CORE.'/init.php');
	}
}
