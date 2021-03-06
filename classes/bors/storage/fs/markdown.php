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

		if(is_file($file = "{$dir}/{$base}.md"))
			return $file;

		if(is_file($file = "{$dir}/$base/main.md"))
			return $file;

		if(is_file($file = "{$dir}/$base/index.md"))
			return $file;

		foreach(bors::$composer_webroots as $d)
		{
			if(is_file($file = secure_path("{$d}/{$rel}/{$base}/main.md")))
				return $file;

			if(is_file($file = secure_path("{$d}/{$rel}/{$base}/index.md")))
				return $file;

			if(is_file($file = secure_path("{$d}/{$rel}/{$base}.md")))
				return $file;
		}

		foreach(bors_dirs() as $d)
		{
			if(is_file($file = secure_path("{$d}/webroot/{$rel}/{$base}/main.md")))
				return $file;

			if(is_file($file = secure_path("{$d}/webroot/{$rel}/{$base}/index.md")))
				return $file;

			if(is_file($file = secure_path("{$d}/webroot/{$rel}/{$base}.md")))
				return $file;
		}

		return false;
	}

	function load($object)
	{
		if(file_exists($object->id()))
			$file = $object->id();
		else
			$file = $this->__find($object);

		if(!$file)
			return $object->set_is_loaded(false);

		debug_log_var('markdown-file', $file);

		$object->set_markup('bors_markup_markdown', false);

		return self::load_from_file($object, $file);
	}

	static function load_from_file($object, $file)
	{
		$content = file_get_contents($file);

		if(preg_match("/^---\n(.+?)\n---\n(.+)$/s", $content, $m))
		{
			// Hard test:
			if(!class_exists('Symfony\Component\Yaml\Yaml'))
				throw new Exception("Can't find yaml extension or Symfony\Component\Yaml.\nGo to composer directory at BORS_CORE level and execute\ncomposer require symfony/yaml=*");

			$content = $m[2];
			try
			{
				$data = bors_data_yaml::parse($m[1]);
			}
			catch(Exception $e)
			{
				bors_debug::syslog('error-yaml-parse', "Error in $file: " . blib_exception::factory($e)->message());
				return $object->set_is_loaded(false);
			}

			foreach(array(
					'Date' => array(
						'create_time',
						'strtotime'
					),
					'Config' => 'config_class',
					'Theme' => 'theme_class',
					'Breadcrumb' => 'nav_name',
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

			$parents = popval($data, 'Parents', []);
			if(empty($parents[0]))
			{
				$ud = url_parse($object->url());
				if(($pd = dirname($ud['path'])) != '/')
					$parents = [$pd.'/'];
				else
					$parents = ['/'];
			}

			$object->set_attr('parents', $parents);

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
			$object->set_title($object->_basename(), false);
//			return $object->set_is_loaded(false);

// Разные трактовки переменных в Markdown:
//	* http://assemble.io/docs/Markdown.html
//	* http://docs.runmyprocess.com/Training/Markdown_Template
//	* http://johnmacfarlane.net/pandoc/README.html
//		$content = 

		$object->set_source($content, false);

		return $object->set_is_loaded(true);
	}
}
