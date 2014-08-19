<?php

function global_key($type,$key)
{
	return empty($GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)])
		? false : $GLOBALS['HTS_GLOBAL_DATA'][md5($type)][md5($key)];
}
