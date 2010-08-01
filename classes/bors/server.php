<?php

class bors_server extends base_object
{
	function can_cached() { return true; }

	static function is_utf8() { return config('internal_charset') == 'utf-8'; }

	static function post_redirector_url($url)
	{
		return "http://{$_SERVER['HTTP_HOST']}/bors-post-redirector.php?go=".urlencode($url);
	}
}
