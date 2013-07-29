<?php

class bors_lib_dir
{
	static function clean_path($dir)
	{
		do
		{
			@rmdir($dir);
			$dir = dirname($dir);
		} while ($dir > '/');
	}
}
