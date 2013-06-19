<?php

bors_function_include('fs/file_put_contents_lock');

class bors_var_cache
{
	function get($var_name, $default=NULL)
	{
		$val = @file_get_contents(config('cache_dir').'/vars/'.$var_name.'.dat');
		if($val)
			return unserialize($val);

		return $default;
	}

	function set($var_name, $value)
	{
		mkpath($dir = config('cache_dir').'/vars', 0777);
		file_put_contents_lock($dir.'/'.$var_name.'.dat', serialize($value));
	}
}
