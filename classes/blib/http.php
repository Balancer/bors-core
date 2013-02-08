<?php

if(function_exists('curl_init'))
	eval('class blib_http extends blib_http_curl { }');
else
	eval('class blib_http extends blib_http_file { }');

