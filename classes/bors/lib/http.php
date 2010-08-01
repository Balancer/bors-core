<?php

class bors_lib_http
{
	function get($url, $raw = false)
	{
		require_once('inc/http.php');
		return http_get_content($url);
	}
}
