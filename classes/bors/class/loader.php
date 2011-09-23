<?php

class bors_class_loader
{
	static function load($class_name, &$args = array())
	{
		if(in_array($class_name, config('classes_skip', array())))
			return false;

		// Если у нас уже загружался соответствующий класс, то возвращаем
		// его реальный(! — например, .yaml) файл, не кешированный.
		if($real_class_file = @$GLOBALS['bors_data']['classes_included'][$class_name])
			return $real_class_file;

		$class_path = str_replace('_', '/', $class_name).'.php';
		$cached_class_file = config('cache_dir').'/classes/'.$class_path;

		$class_info_path = str_replace('_', '/', $class_name).'.ini';
		$cached_class_info_file = config('cache_dir').'/classes/'.$class_info_path;

		if(file_exists($cached_class_file) && file_exists($cached_class_info_file))
		{
			$info = parse_ini_file($cached_class_info_file);
			$real_class_file = $info['real_class_file'];
			if(file_exists($real_class_file) && ($info['cached_class_filemtime'] >= filemtime($real_class_file)))
			{
				require_once($cached_class_file);
				return $GLOBALS['bors_data']['classes_included'][$class_name] = $real_class_file;
			}
			@unlink($cached_class_file);
			@unlink($cached_class_info_file);
		}

		return self::find_and_include($class_name, $args);
	}

	private function find_and_include($class_name, &$args = array())
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
//			echo "check {$dir}/classes/{$class_path}{$class_file}.php  <br/>\n";
			if(file_exists($file_name = "{$dir}/classes/{$class_path}{$class_file}.php"))
			{
				require_once($file_name);
				$GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
				return $file_name;
			}

			if(file_exists($file_name = "{$dir}/classes/bors/{$class_path}{$class_file}.php"))
			{
				require_once($file_name);
				$GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
				return $file_name;
			}

			if(file_exists($file_name = "{$dir}/classes/inc/$class_name.php"))
			{
				require_once($file_name);
				$GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
				return $file_name;
			}

//			echo "Find {$dir}/classes/{$class_path}{$class_file}.yaml<br/>\n";
			if(file_exists($file_name = "{$dir}/classes/{$class_path}{$class_file}.yaml"))
			{
				bors_class_loader_yaml::load($class_name, $file_name);
				$GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
				return $file_name;
			}
		}

		if(class_exists($class_name))
			return class_include(get_parent_class($class_name));

		if(empty($args['host']))
			return false;

		$data = bors_vhost_data($args['host']);
		if(file_exists($file_name = "{$data['bors_site']}/classes/{$class_path}{$class_file}.php"))
		{
			require_once($file_name);
			$GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
			$args['need_check_to_public_load'] = true;
			return $file_name;
		}

		if(file_exists($file_name = "{$data['bors_site']}/classes/bors/{$class_path}{$class_file}.php"))
		{
			require_once($file_name);
			$GLOBALS['bors_data']['classes_included'][$class_name] = $file_name;
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

		$ini_file = str_replace('.php', '.ini', $cached_class_file);
		mkpath(dirname($ini_file), 0755);
		bors_file_ini::write($ini_file, $data);
	}
}
