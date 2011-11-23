<?php

bors_function_include('debug/counting');

function global_key($type,$key)
{
	return @$GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)] ? $GLOBALS['HTS_GLOBAL_DATA'][md5($type)][md5($key)] : false;
}

function is_global_key($type,$key)
{
	if(@$GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)])
	{
		debug_count_inc('global_key_count_hit');
		return true;
	}
	else
	{
		debug_count_inc('global_key_count_miss');
		return false;
	}
}

function set_global_key($type, $key, $value)
{
	$GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)] = true;
	return $GLOBALS['HTS_GLOBAL_DATA'][md5($type)][md5($key)] = $value;
}

function clear_global_key($type,$key)
{
	unset($GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)]);
	if(!empty($GLOBALS['HTS_GLOBAL_DATA'][md5($type)][md5($key)]))
		unset($GLOBALS['HTS_GLOBAL_DATA'][md5($type)][md5($key)]);
}

function global_keys_clean()
{
	unset($GLOBALS['bors_data']['global']);
	unset($GLOBALS['HTS_GLOBAL_DATA']);
}
