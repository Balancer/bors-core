<?php

function global_key($type,$key)
{
	return @$GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)] ? $GLOBALS['HTS_GLOBAL_DATA'][md5($type)][md5($key)] : false;
}
