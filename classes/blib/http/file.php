<?php

class blib_http_file extends blib_http_abstract
{
	function __unit_test($test)
	{
		$x = blib_http::get('http://google.com');
	}
}
