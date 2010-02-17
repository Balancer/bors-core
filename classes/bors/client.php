<?php

class bors_client extends base_object
{
	function can_cached() { return false; }
	function is_bot() { return @$GLOBALS['client']['is_bot']; }
	function ip() { return @$_SERVER['REMOTE_ADDR']; }
	function referer() { return @$_SERVER['HTTP_REFERER']; }
	function agent() { return @$_SERVER['HTTP_USER_AGENT']; }
}
