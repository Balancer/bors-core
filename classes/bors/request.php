<?php

class bors_request extends base_object
{
	function can_cached() { return true; }

	static function is_utf8() { return config('internal_charset') == 'utf-8'; }

	static function data($key = NULL, $default = NULL) { return $key ? defval($_GET, $key, $default) : $_GET; }

	function url() { return $GLOBALS['bors_full_request_url']; }
	function referer() { return @$_SERVER['HTTP_REFERER']; }
}
