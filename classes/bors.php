<?php

class bors
{
	static function init()
	{
		if(!defined('BORS_CORE'))
			define('BORS_CORE', dirname(dirname(__FILE__)));

		require_once(BORS_CORE.'/init.php');
	}

	function route_view($url = NULL, $host = NULL, $port = NULL)
	{
		if(!$url)
			$url = $_SERVER['REQUEST_URI'];

		echo "Try load $url<br/>\n";
		$view = bors_load_uri($url);
		echo $view;
		return $view;
	}
}
