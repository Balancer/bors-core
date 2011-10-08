<?php

class bors_lib_debug
{
	function request_info_string()
	{
		return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'; referer='.@$_SERVER['HTTP_REFERER'] . '; IP='.@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT'];
	}
}
