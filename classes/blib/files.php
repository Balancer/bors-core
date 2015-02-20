<?php

class blib_files
{
	static function tmp($prefix = '', $ext = '.tmp')
	{
		if($prefix)
			$prefix .= '-';

		$file = tempnam(config('cache_dir'), $prefix);

		$GLOBALS['bors_data']['shutdown_handlers'][] = array(
			'callback' => array('blib_files', '__shutdown_tmp_unlink'),
			'arg' => $file . $ext,
		);

		$GLOBALS['bors_data']['shutdown_handlers'][] = array(
			'callback' => array('blib_files', '__shutdown_tmp_unlink'),
			'arg' => $file,
		);

//		file_put_contents($file, bors_debug::trace());

		return $file . $ext;
	}

	static function __shutdown_tmp_unlink($file)
	{
		if(file_exists($file))
			unlink($file);
	}

	static function __dev()
	{
		$tmp = blib_files::tmp();
		echo "tmp=$tmp\n";
		file_put_contents($tmp, "qwe\n");
		echo file_get_contents($tmp);
	}
}
