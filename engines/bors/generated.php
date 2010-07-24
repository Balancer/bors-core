<?php

if(function_exists('mb_strtolower') && config('internal_charset') == 'utf-8')
{
	// Фиг его знает, с чего PHP без этого перестал работать в консоли
	ini_set('mbstring.internal_encoding', config('internal_charset'));
	function bors_upper($str) { return mb_strtoupper($str); }
	function bors_lower($str) { return mb_strtolower($str); }
	function bors_strlen($str) { return mb_strlen($str); }
	function bors_substr($str, $start, $length=NULL) { return is_null($length) ? mb_substr($str, $start) : mb_substr($str, $start, $length); }
	function bors_strpos($str, $need, $start=NULL) { return is_null($start) ? mb_strpos($str, $need) : mb_strpos($str, $need, $start); }
	function bors_ucfirst($str) { return mb_substr(mb_strtoupper($string), 0, 1).substr(mb_strtolower($string), 1); }
}
else
{
	function bors_lower($str) { return strtolower($str); }
	function bors_upper($str) { return strtoupper($str); }
	function bors_strlen($str) { return strlen($str); }
	function bors_substr($str, $start, $length=NULL) { return is_null($length) ? substr($str, $start) : substr($str, $start, $length); }
	function bors_strpos($str, $need, $start=NULL) { return is_null($start) ? strpos($str, $need) : strpos($str, $need, $start); }
	function bors_ucfirst($str) { return ucfirst($string); }
}

eval('class bors_log extends '.config('log.class', 'bors_log_stub').' { } ');
