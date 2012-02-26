<?php

function set_global_key($type, $key, $value)
{
	$GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)] = true;
	return $GLOBALS['HTS_GLOBAL_DATA'][md5($type)][md5($key)] = $value;
}
