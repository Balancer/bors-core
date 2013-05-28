<?php

class bors_class_loader
{
	static function file($class_name)
	{
		if($real_class_file = @$GLOBALS['bors_data']['classes_included'][$class_name])
			return $real_class_file;

		if($c = @$GLOBALS['bors.composer.class_loader'])
		{
			$map = $c->getClassMap();
			if($real_class_file = @$map[$class_name])
				return $real_class_file;
		}

		return NULL;
	}

	static function load($class_name, &$args = array())
	{
//		echo "Check class $class_name<br/>\n";
		if(in_array($class_name, config('classes_skip', array())))
			return false;

		// Если у нас уже загружался соответствующий класс, то возвращаем
		// его реальный(! — например, .yaml) файл, не кешированный.
		if($real_class_file = @$GLOBALS['bors_data']['classes_included'][$class_name])
			return $real_class_file;

		if(config('cache_code_monolith')
			&& empty($GLOBALS['bors_data']['classes_cache_content'])
			&& file_exists($classes_cache_file = config('cache_dir') . '/classes.php')
		)
		{
			$GLOBALS['bors_data']['classes_cache_content'] = file_get_contents($classes_cache_file);
			require_once($classes_cache_file);

			if($real_class_file = @$GLOBALS['bors_data']['classes_included'][$class_name])
				return $real_class_file;
		}

		$class_base = str_replace('_', '/', $class_name);
		$class_path = $class_base.'.php';
		$cached_class_file = config('cache_dir').'/classes/'.$class_path;

		$class_info_path = $class_base.'.ini';
		$cached_class_info_file = config('cache_dir').'/classes/'.$class_info_path;

		if(file_exists($cached_class_file) && file_exists($cached_class_info_file))
		{
			$info = parse_ini_file($cached_class_info_file);
			$real_class_file = $info['real_class_file'];
			if(file_exists($real_class_file) && ($info['cached_class_filemtime'] >= filemtime($real_class_file)))
			{
				if(!($php_inc = @$info['real_class_php_inc_file'])
						|| !file_exists($php_inc)
						|| (@$info['real_class_php_inc_filemtime'] >= filemtime($php_inc)))
				{
//					echo "Load cached class $class_name<br/>\n";
					require_once($cached_class_file);
					return $GLOBALS['bors_data']['classes_included'][$class_name] = $real_class_file;
				}
			}

			@unlink($cached_class_file);
			@unlink($cached_class_info_file);
		}

		return self::find_and_include($class_name, $args);
	}

	private static function find_and_include($class_name, &$args = array())
	{
		$class_path = "";
		$class_file = $class_name;

		if(preg_match("!^(.+/)([^/]+)$!", str_replace("_", "/", $class_name), $m))
		{
			$class_path = $m[1];
			$class_file = $m[2];
		}

		foreach(bors_dirs() as $dir)
		{
			if(file_exists($file_name = "{$dir}/classes/{$class_path}{$class_file}.php"))
				return self::load_and_cache($class_name, $file_name);

			if(file_exists($file_name = "{$dir}/classes/bors/{$class_path}{$class_file}.php"))
				return self::load_and_cache($class_name, $file_name);

			if(file_exists($file_name = "{$dir}/classes/inc/$class_name.php"))
				return self::load_and_cache($class_name, $file_name);

			if(file_exists($file_name = "{$dir}/classes/{$class_path}{$class_file}.yaml"))
			{
				bors_class_loader_yaml::load($class_name, $file_name);
				$GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
				return $file_name;
			}
		}

		if(class_exists($class_name, false))
			return class_include(get_parent_class($class_name));

		if(empty($args['host']))
			return false;

		$data = bors_vhost_data($args['host']);
		if(file_exists($file_name = "{$data['bors_site']}/classes/{$class_path}{$class_file}.php"))
		{
			self::load_and_cache($class_name, $file_name);
			$args['need_check_to_public_load'] = true;
			return $file_name;
		}

		if(file_exists($file_name = "{$data['bors_site']}/classes/bors/{$class_path}{$class_file}.php"))
		{
			self::load_and_cache($class_name, $file_name);
			$args['need_check_to_public_load'] = true;
			return $file_name;
		}

		return false;
	}

	static function cache_make_info($class_name, $class_file, $cached_class_file)
	{
		$data = array(
			'real_class_file' => $class_file,
			'cached_class_filemtime' => filemtime($class_file),
		);

		if(file_exists($file = preg_replace('/^(.+)\.\w+$/', '$1.inc.php', $class_file)))
		{
			$data['real_class_php_inc_file'] = $file;
			$data['real_class_php_inc_filemtime'] = filemtime($file);
		}

		$ini_file = str_replace('.php', '.ini', $cached_class_file);
		mkpath(dirname($ini_file), 0755);
		bors_file_ini::write($ini_file, $data);
	}

	static function load_and_cache($class_name, $class_file)
	{
//		echo "Find class $class_name = $class_file<br/>\n";
		if(!class_exists($class_name, false))
		{
			if(config('cache_code_monolith'))
			{
				static $classes_cache_file = NULL;
				if(!$classes_cache_file)
					$classes_cache_file = config('cache_dir') . '/classes.php';

				if(!file_exists($classes_cache_file))
					file_put_contents($classes_cache_file, "<?php\n");

				if(empty($GLOBALS['bors_data']['classes_cache_content']))
					$GLOBALS['bors_data']['classes_cache_content'] = file_get_contents($classes_cache_file);
				require_once($classes_cache_file);
				if(class_exists($class_name))
					return $GLOBALS['bors_data']['classes_included'][$class_name] = $class_file;

				require_once($class_file);

//				echo "Store class $class_name to $classes_cache_file<br/>\n";
				$class_code = file_get_contents($class_file);
				$class_code = "\n".trim(preg_replace('/^<\?php/', '', $class_code))."\n";
//				$class_code = preg_replace("/class $class_name/", "if(!class_exists('$class_name'))\n{  class $class_name", $class_code);
				$class_code .= "\$GLOBALS['bors_data']['classes_included']['{$class_name}'] = '$class_file';\n";
//				$class_code .= "}\n";
				$GLOBALS['bors_data']['classes_included'][$class_name] = $class_file;
				$GLOBALS['bors_data']['classes_cache_content'] .= $class_code;
				$GLOBALS['bors_data']['classes_cache_content_updated'] = true;
			}
			else
				require_once($class_file);
		}
//		else
//			echo "Class $class_name already loaded<br/>\n";

//		if(preg_match('/photoreport/', $class_name)) echo "Find class $class_name: $class_file<br/>\n";
		return $GLOBALS['bors_data']['classes_included'][$class_name] = $class_file;
	}
}

$GLOBALS['bors_data']['classes_included']['bors_class_loader'] = __FILE__;
