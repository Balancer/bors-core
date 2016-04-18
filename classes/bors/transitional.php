<?php

class bors_transitional
{
	static function init()
	{
		if(!defined('COMPOSER_ROOT'))
			define('BORS_CORE', realpath(__DIR__.'/../../'));

		if(!defined('BORS_CORE'))
			define('BORS_CORE', COMPOSER_ROOT.'/vendor/balancer/bors-core');

		require_once __DIR__.'/../../inc/functions/locale/ec.php';
	}

	static function function_include($req_name)
	{
		static $defined = array();

		if(preg_match('!^(\w+)/(\w+)$!', $req_name, $m))
		{
			$path = $m[1];
			$name = $m[2];
		}
		else
		{
			$path = '';
			$name = $req_name;
		}

		if(!empty($defined[$req_name]))
			return;

		$defined[$req_name] = true;

		return require_once(__DIR__.'/../../inc/functions/'.$req_name.'.php');
	}
}

if(!function_exists('bors_url_map'))
{
	function bors_url_map($map_array)
	{
		global $bors_map;

		if(empty($bors_map))
			$bors_map = [];

		$bors_map = array_merge($bors_map, $map_array);
	}
}
