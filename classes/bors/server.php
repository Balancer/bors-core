<?php

class bors_server extends base_object
{
	function can_cached() { return true; }

	static function is_utf8() { return config('internal_charset') == 'utf-8'; }
}
