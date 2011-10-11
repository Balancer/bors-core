<?php

class bors_lib_dir
{
	function clean_path($dir)
	{
		do
		{
			@rmdir($dir);
			$dir = dirname($dir);
		} while ($dir > '/');
	}
}
