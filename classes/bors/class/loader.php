<?php

class bors_class_loader
{
	static $class_files = array();
	static $class_file_mtimes = array();

	static function file($class_name, $dirs = array())
	{
		if(!empty($GLOBALS['bors_data']['classes_included'][$class_name]))
			return $GLOBALS['bors_data']['classes_included'][$class_name];

		if($c = @$GLOBALS['bors.composer.class_loader'])
		{
			$map = $c->getClassMap();
			if($real_class_file = @$map[$class_name])
				return $real_class_file;
		}

		$class_path = "";
		$class_file = $class_name;

		if(preg_match("!^(.+/)([^/]+)$!", str_replace("_", "/", $class_name), $m))
		{
			$class_path = $m[1];
			$class_file = $m[2];
		}

		foreach($dirs as $dir)
		{
			if(file_exists($file_name = "{$dir}/classes/{$class_path}{$class_file}.php"))
				return $GLOBALS['bors_data']['classes_included'][$class_name] = self::load_and_cache($class_name, $file_name);

			if(file_exists($file_name = "{$dir}/classes/{$class_path}{$class_file}.yaml"))
			{
				bors_class_loader_yaml::load_from_file($class_name, $file_name);
				$GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
				return $GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
			}
		}

		$ref = new ReflectionClass($class_name);
		if($class_file = $ref->getFileName())
			return $GLOBALS['bors_data']['classes_included'][$class_name] = $class_file;

		return NULL;
	}

	static function load_file($class_name, &$args = array())
	{
		if(is_object($class_name))
		{
			bors_debug::syslog('class-loader-error', "Try to load class with name = object ".$class_name->debug_title());
			$class_name = $class_name->class_name();
		}

		if(!preg_match('/^[\\\\\w]{2,}$/', $class_name)
			|| preg_match('/^Smarty_Resource_Custom/', $class_name) // Чтобы не мусорило логи
		)
			return false;

		// Заставляем сработать class_loader
		class_exists($class_name);

		static $skips = NULL;
		if(is_null($skips))
			$skips = \B2\Cfg::get('classes_skip', array());

		if(in_array($class_name, $skips))
			return false;

		static $cachd = NULL;
		if(is_null($cachd))
			$cachd = \B2\Cfg::get('cache_dir').'/classes/';

		// Если у нас уже загружался соответствующий класс, то возвращаем
		// его реальный(! — например, .yaml) файл, не кешированный.

		if(!empty($GLOBALS['bors_data']['classes_included'][$class_name]))
			return $GLOBALS['bors_data']['classes_included'][$class_name];

		if(class_exists($class_name))
		{
			$reflector = new ReflectionClass($class_name);
			if($class_file = $reflector->getFileName())
			{
				// Требуется проверка существования файла, иначе не определяются случаи с классами, определёнными по eval.
				if(file_exists($class_file))
					return $GLOBALS['bors_data']['classes_included'][$class_name] = $class_file;
			}
		}

		$class_base = str_replace('_', '/', $class_name);

		$cached_class_info_json = $cachd.$class_base.'.data.json';

		if(file_exists($cached_class_info_json))
		{
			$info = json_decode(file_get_contents($cached_class_info_json), true);

			if(!empty($info['class_file_real'])
				&& file_exists($info['class_file_real'])
				&& ($info['was_changed'] >= filemtime($info['class_file_real']))
				&& !empty($info['class_file_php'])
				&& file_exists($info['class_file_php'])
			)
			{
				bors_object::$__cache_data[$class_name] = $info;
				require_once($info['class_file_php']);

				return $GLOBALS['bors_data']['classes_included'][$class_name] = $info['class_file_real'];
			}
		}

		$class_file = self::find_and_include($class_name, $args);

		if($class_file && preg_match('/\.php$/', $class_file))
		{
			self::set_class_cache_data($class_name, $class_file, 'class_file_real', $class_file);
			self::set_class_cache_data($class_name, $class_file, 'class_file_php', $class_file);
		}

		return $GLOBALS['bors_data']['classes_included'][$class_name] = $class_file;
	}

	private static function find_and_include($class_name, &$args = array())
	{
		if(\B2\Cfg::get('debug.profiling'))
			bors_debug::syslog('profiling', "Load non cached class $class_name; bors_dirs=".print_r(bors_dirs(), true));

		$class_path = "";
		$class_file = $class_name;

		if(preg_match("!^(.+/)([^/]+)$!", str_replace("_", "/", $class_name), $m))
		{
			$class_path = $m[1];
			$class_file = $m[2];
		}

		foreach(bors::$composer_class_dirs as $dir)
		{
			if(file_exists($file_name = "{$dir}/{$class_path}{$class_file}.php"))
				return self::load_and_cache($class_name, $file_name);

			if(file_exists($file_name = "{$dir}/{$class_path}{$class_file}.yaml"))
			{
				bors_class_loader_yaml::load_from_file($class_name, $file_name);
				$GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
				return $file_name;
			}
		}

		foreach(bors_dirs() as $dir)
		{
			if(file_exists($file_name = "{$dir}/classes/{$class_path}{$class_file}.php"))
				return self::load_and_cache($class_name, $file_name);

			if(file_exists($file_name = "{$dir}/classes/bors/{$class_path}{$class_file}.php"))
				return self::load_and_cache($class_name, $file_name);

			if(file_exists($file_name = "{$dir}/classes/{$class_path}{$class_file}.yaml"))
			{
				bors_class_loader_yaml::load_from_file($class_name, $file_name);
				$GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
				return $file_name;
			}
		}

		if(file_exists($file_name = realpath(__DIR__.'/../../../classes/inc/$class_name.php')))
			return self::load_and_cache($class_name, $file_name);


		if(class_exists($class_name, false))
			return class_include(get_parent_class($class_name));

		if(empty($args['host']))
			return false;

		$data = bors_vhost_data($args['host']);
//		echo '<xmp>'; var_dump($data); echo '</xmp>';
		if($data && !empty($data['bors_site']) && file_exists($file_name = "{$data['bors_site']}/classes/{$class_path}{$class_file}.php"))
		{
			self::load_and_cache($class_name, $file_name);
			$args['need_check_to_public_load'] = true;
			return $file_name;
		}

		if($data && !empty($data['bors_site']) && file_exists($file_name = "{$data['bors_site']}/classes/bors/{$class_path}{$class_file}.php"))
		{
			self::load_and_cache($class_name, $file_name);
			$args['need_check_to_public_load'] = true;
			return $file_name;
		}

		return false;
	}

	static function load_and_cache($class_name, $class_file)
	{
		if(!class_exists($class_name, false))
			require_once($class_file);

		return $GLOBALS['bors_data']['classes_included'][$class_name] = $class_file;
	}

	static function set_class_cache_data($class_name, $class_file, $var_name, $value)
	{
		bors_object::$__cache_data[$class_name][$var_name] = $value;
		bors_object::$__cache_data[$class_name]['was_changed'] = time();

		if(empty($GLOBALS['bors_data']['classes_cache_updates'][$class_name]))
			$GLOBALS['bors_data']['classes_cache_updates'][$class_name] = [
				'class_file' => $class_file,
				'cache_data' => &bors_object::$__cache_data[$class_name],
			];

		return $value;
	}

	static function classes_cache_data_save($class_name, $data, $class_file = NULL)
	{
		if(empty($data['was_changed']))
			return;

		$cached_class_info_json = \B2\Cfg::get('cache_dir').'/classes/'
			.str_replace('_', '/', $class_name)
			.'.data.json';

		if(!$class_file)
			$class_file = bors_foo($class_name)->class_file();

		if(!$class_file)
			echo "Can't find class_file for $class_name</br>\n";

		$data['class_mtime'] = filemtime($class_file);

		require_once BORS_CORE.'/inc/functions/fs/file_put_contents_lock.php';
		mkpath(dirname($cached_class_info_json), 0775);

		$flags = 0;
		if(version_compare(PHP_VERSION, '5.4') >= 0)
			$flags = JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE;

		file_put_contents_lock($cached_class_info_json, json_encode($data, $flags));
	}
}

$GLOBALS['bors_data']['classes_included']['bors_class_loader'] = __FILE__;
