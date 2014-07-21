<?php

class blib_http_file extends blib_http_abstract
{
	static function __unit_test_disabled($test)
	{
		$x = blib_http::get('http://google.com');
	}
}
