<?php

class bors_client extends base_object
{
	function can_cached() { return false; }
	function is_bot() { return @$GLOBALS['client']['is_bot']; }
	function ip()
	{
		$ip = @$_SERVER['REMOTE_ADDR'];
		if(empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $ip;

		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	function referer() { return @$_SERVER['HTTP_REFERER']; }
	function agent() { return @$_SERVER['HTTP_USER_AGENT']; }
	function url()
	{
		$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'];
		if($_SERVER['QUERY_STRING'])
			$url .= '?'.$_SERVER['QUERY_STRING'];

		return $url;
	}

	function place()
	{
		require_once('inc/clients/geoip-place.php');
		return geoip_place($this->ip());
	}

	function flag()
	{
		require_once('inc/clients/geoip-place.php');
		return geoip_flag($this->ip());
	}
}
