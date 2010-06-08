<?php

class bors_storage_fs_markdown extends base_null
{
	private function __find($object)
	{
		$dir = $object->dir();
		$rel = secure_path(str_replace($_SERVER['DOCUMENT_ROOT'], '/', $dir));

		if(file_exists($file = "{$dir}/index.markdown"))
			return $file;

		if(file_exists($file = "{$dir}.mdml"))
			return $file;

		if(file_exists($file = "{$dir}.markdown"))
			return $file;

		foreach(bors_dirs() as $d)
		{
			if(file_exists($file = secure_path("{$d}/data/fs/{$rel}.mdml")))
				return $file;

			if(file_exists($file = secure_path("{$d}/data/fs/{$rel}/main.mdml")))
				return $file;

			if(file_exists($file = secure_path("{$d}/data/fs/{$rel}/index.mdml")))
				return $file;

			if(file_exists($file = secure_path("{$d}/data/fs/{$rel}.markdown")))
				return $file;

			if(file_exists($file = secure_path("{$d}/data/fs/{$rel}/index.markdown")))
				return $file;
		}

		return false;
	}

	function load($object)
	{
		$file = $this->__find($object);
		if(!$file)
			return $object->set_loaded(false);

		$object->set_markup('bors_markup_markdown', false);

		$content = $object->cs_f2i(file_get_contents($file));
		if(preg_match('/(^|\n)(.+?)\n(=+)\n/s', $content, $m))
			$object->set_title($m[2], false);

		$object->set_source($content, false);

		return $object->set_loaded(true);
	}

	function save($object)
	{
		debug_exit(ec('Сохранение markdown-файлов ещё не реализовано'));
	}
}
