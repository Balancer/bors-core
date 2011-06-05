<?php

class bors_data_meta extends bors_object
{
	// Читаем содержимое файла из одного из data-каталогов в bors_dirs()
	static function read($file, $base = '/')
	{
		if(file_exists($file) && is_readable($file))
			return array(
				'content' => file_get_contents($file),
				'mtime' => filemtime($file),
			);

		foreach(bors_dirs() as $dir)
			if(file_exists($fn = $dir.$base.'/'.$file) && is_readable($fn))
				return array(
					'content' => file_get_contents($fn),
					'mtime' => filemtime($fn),
					'file' => $fn,
				);

		return NULL;
	}
}
