<?php

function clear_global_key($type,$key)
{
	unset($GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)]);
	if(!empty($GLOBALS['HTS_GLOBAL_DATA'][md5($type)][md5($key)]))
		unset($GLOBALS['HTS_GLOBAL_DATA'][md5($type)][md5($key)]);
}
