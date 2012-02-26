<?php

bors_function_include('debug/count');

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
