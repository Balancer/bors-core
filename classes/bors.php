<?php

class bors
{
	static function init()
	{
		if(!defined('BORS_CORE'))
			define('BORS_CORE', dirname(dirname(__FILE__)));

		require_once(BORS_CORE.'/init.php');
	}

	static function log()
	{
		return bors_log_monolog::instance();
	}

	function route_view($url = NULL, $host = NULL, $port = NULL)
	{
		if(!$url)
			$url = $_SERVER['REQUEST_URI'];

		$view = bors_load_uri($url);
		return $view;
	}

	static function run()
	{
		self::init();
		require_once(BORS_CORE.'/main.php');
	}
}
