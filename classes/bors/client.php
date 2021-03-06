<?php

class bors_client extends bors_object_simple
{
	function can_cached() { return false; }
	function is_bot() { return empty($GLOBALS['client']['is_bot']) ? false : $GLOBALS['client']['is_bot']; }
	function is_crawler() { return @$GLOBALS['client']['is_crawler']; }

	static function factory($ip) { return bors_load(__CLASS__, $ip); }

	function ip()
	{
		if($this->id())
			return $this->id();

		if(empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return empty($_SERVER['REMOTE_ADDR']) ? NULL : $_SERVER['REMOTE_ADDR'];

		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	function referer() { return @$_SERVER['HTTP_REFERER']; }
	function agent() { return empty($_SERVER['HTTP_USER_AGENT']) ? NULL : $_SERVER['HTTP_USER_AGENT']; }
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
