<?php

/**
	Класс хранения данных HTML-страниц в виде *.bbh файлов в каталогах data/fs.
	Пока - только чтение.
*/

class bors_storage_fs_bbh extends bors_storage
{
	private function __find($object)
	{
		$dir = $object->dir();
		$rel = secure_path(str_replace(bors()->server()->root(), '/', $dir));

		if(file_exists($file = "{$dir}/index.bbh"))
			return $file;

		if(file_exists($file = "{$dir}.bbh"))
			return $file;

		foreach(bors_dirs() as $d)
		{
			if(file_exists($file = secure_path("{$d}/data/fs/{$rel}.bbh")))
				return $file;

			if(file_exists($file = secure_path("{$d}/data/fs/{$rel}/index.bbh")))
				return $file;

			if(file_exists($file = secure_path("{$d}/data/fs/{$rel}/main.bbh")))
				return $file;
		}

		return false;
	}

	function load($object)
	{
		$file = $this->__find($object);
		if(!$file)
			return $object->set_loaded(false);

		$object->set_markup('bors_markup_lcmlbbh', false);

		$content = $object->cs_f2i(file_get_contents($file));
		if(preg_match('/^(.+)\n(=+)\n(.+)$/s', $content, $m))
		{
			$object->set_title($m[1], false);
			$content = $m[3];
		}

		$object->set_source($content, false);

		return $object->set_loaded(true);
	}
}
