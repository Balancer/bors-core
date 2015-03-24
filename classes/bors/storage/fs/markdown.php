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

		if(is_file($file = "{$dir}.md"))
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
		}

		return false;
	}

	function load($object)
	{
		$file = $this->__find($object);
		if(!$file)
			return $object->set_is_loaded(false);

		$object->set_markup('bors_markup_markdown', false);

		return self::load_from_file($object, $file);
	}

	static function load_from_file($object, $file)
	{
		$content = file_get_contents($file);

		if(preg_match("/^---\n(.+?)\n---\n(.+)$/s", $content, $m))
		{
			$content = $m[2];
			try
			{
				$data = bors_data_yaml::parse($m[1]);
			}
			catch(Exception $e)
			{
				bors_debug::syslog('yaml-parse-error', "Error in $file: " . blib_exception::factory($e)->message());
				return $object->set_is_loaded(false);
			}

			foreach(array(
					'Date' => array(
						'create_time',
						'strtotime'
					),
					'Config' => 'config_class'
			) as $md => $field)
			{
				if(!empty($data[$md]))
				{
					if(is_array($field))
						$data[$field[0]] = call_user_func($field[1], $data[$md]);
					else
						$data[$field] = $data[$md];

					unset($data[$md]);
				}
			}

			foreach($data as $key => $value)
			{
				$object->set_attr($key, $value);
				// Хм. Атрибуты не всегда работают.
				$object->set($key, $value, false);
			}
		}

		if(preg_match('/^#\s+(.+?)\s+#$/m', $content, $m))
		{
			$object->set_title($m[1], false);
			$content = preg_replace('/^#\s+(.+?)\s+#$/m', '', $content);
		}
		elseif(preg_match('/^#\s+(.+)$/m', $content, $m))
		{
			$object->set_title($m[1], false);
			$content = preg_replace('/^#\s+(.+)$/m', '', $content);
		}
		elseif(preg_match("/(^|\n)(.+?)\n(=+)\n/s", $content, $m))
		{
			$object->set_title($m[2], false);
			$content = preg_replace("/(^|\n)(.+?)\n(=+)\n/", '', $content, 1);
		}

		if(!$object->title_true())
			return $object->set_is_loaded(false);

// Разные трактовки переменных в Markdown:
//	* http://assemble.io/docs/Markdown.html
//	* http://docs.runmyprocess.com/Training/Markdown_Template
//	* http://johnmacfarlane.net/pandoc/README.html
//		$content = 

		$object->set_source($content, false);

		return $object->set_is_loaded(true);
	}
}
