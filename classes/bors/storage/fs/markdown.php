<?php

/**
	Класс хранения данных HTML-страниц в виде markdown-файлов в каталогах data/fs.
	Пока - только чтение.
*/

class bors_storage_fs_markdown extends bors_storage
{
	private function __find($object)
	{
		$dir = $object->dir();
		$base = $object->_basename();

		if(preg_match('/\.php$/', $base)) // Хардкод, но что делать? :-/
			return false;

		$rel = secure_path(str_replace(bors()->server()->root(), '/', $dir));

		if($base && is_file($file = "{$dir}/{$base}.md"))
			return $file;

		if($base && is_file($file = "{$dir}/{$base}"))
			return $file;

		if(is_file($file = "{$dir}/index.markdown"))
			return $file;

		if(is_file($file = "{$dir}.md"))
			return $file;

		if(is_file($file = "{$dir}.markdown"))
			return $file;

		foreach(bors_dirs() as $d)
		{
			if(is_file($file = secure_path("{$d}/webroot/{$rel}.md")))
				return $file;

			if($base && is_file($file = secure_path("{$d}/webroot/{$rel}/{$base}.md")))
				return $file;

			if(is_file($file = secure_path("{$d}/webroot/{$rel}/main.md")))
				return $file;

			if(is_file($file = secure_path("{$d}/webroot/{$rel}/index.md")))
				return $file;

			if(is_file($file = secure_path("{$d}/webroot/{$rel}.markdown")))
				return $file;

			if(is_file($file = secure_path("{$d}/webroot/{$rel}/index.markdown")))
				return $file;
		}

		return false;
	}

	function load($object)
	{
		$file = $this->__find($object);
		if(!$file)
			return $object->set_is_loaded(false);

		$object->set_markup('bors_markup_markdown', false);

		$content = $object->cs_f2i(file_get_contents($file));
		if(preg_match('/(^|\n)(.+?)\n(=+)\n/s', $content, $m))
			$object->set_title($m[2], false);

		if(!$object->title_true())
			return $object->set_is_loaded(false);

		$object->set_source($content, false);

		return $object->set_is_loaded(true);
	}
}
