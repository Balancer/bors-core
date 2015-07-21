<?php

class bors_funcs
{
	static function noop() { }

	static function init()
	{

		if(!defined('BORS_CORE'))
			define('BORS_CORE', dirname(dirname(__DIR__)));

		if(!defined('BORS_EXT'))
			define('BORS_EXT', dirname(BORS_CORE).'/bors-ext');

		if(!defined('BORS_SITE'))
		{
			if(empty($_SERVER['HTTP_HOST']))
				define('BORS_SITE', '/var/www/'.$_SERVER['HOME']);
			else
				define('BORS_SITE', '/var/www/'.$_SERVER['HTTP_HOST']);
		}

//		require BORS_CORE.'/classes/bors/class/loader.php';
	}
}

require_once __DIR__.'/../../inc/funcs.php';
