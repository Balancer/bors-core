<?php

class storage_fs_separate extends base_null
{
	function load($object)
	{
		if(!($id = $object->called_url()))
			$id = $object->id();

		if(!$id)
			return false;

		$url_data = url_parse($id);
		$path = $url_data['path'];
		$dir = $url_data['local_path'];
		$pfx = '';

		if($found = file_exists($dir.'/.title.txt'))
			$pfx = '.';
		else
		{
			foreach(bors_dirs(true) as $base)
			{
				if(file_exists(($dir = "{$base}/data/fs{$path}").'title.txt'))
				{
					$found = true;
					break;
				}

				if(file_exists(($dir = "{$base}/data/fs-separate{$path}").'title.txt'))
				{
					$found = true;
					break;
				}
			}
		}

		if(!$found)
			return $object->set_loaded(false);

		debug_log_var('fs.separate.dir', $dir);

		// По дефолту в separate разрешён HTML и все BB-тэги.
		$object->set_html_disable(false, false);
		$object->set_lcml_tags_enabled(NULL, false);

		$object->set_attr('storage_base_dir', $dir, false);
		$object->set_attr('storage_file_prefix', $pfx, false);
		$create_time = time()+99999;
		$modify_time = 0;

		$d = dir($dir);
		$loaded_fields = array();
		while(false !== ($entry = $d->read()))
		{
			if(preg_match("!".preg_quote($pfx)."\[(\w+)\]\.txt$!", $entry, $m))
			{
				$data = array();
				foreach(file("{$dir}/{$entry}") as $s)
					$data[] = $object->cs_f2i($s);

				if(method_exists($object, $method = "set_{$m[1]}"))
					$object->$method($data, false);
				else
					$object->set($m[1], $data, false);

				$loaded_fields[$m[1]] = $data;
			}
			elseif(preg_match("!".preg_quote($pfx)."(\w+)\.txt$!", $entry, $m))
			{
				$data = $object->cs_f2i(file_get_contents("{$dir}/{$entry}"));
				if(method_exists($object, $method = "set_{$m[1]}"))
					$object->$method($data, false);
				else
					$object->set($m[1], $data, false);

				if($m[1] == 'create_time')
					$create_time = -1;
				elseif($m[1] == 'modify_time')
					$modify_time = -1;
				elseif($m[1] == 'title' || $m[1] == 'source')
				{
					if($create_time != -1)
						$create_time = min($create_time, filectime("{$dir}/{$entry}"));
					if($modify_time != -1)
						$modify_time = max($modify_time, filemtime("{$dir}/{$entry}"));
				}

				$loaded_fields[$m[1]] = $data;
			}
		}
		$d->close();

		if($create_time > 0)
			$object->set_create_time($create_time, true);
		if($modify_time > 0)
			$object->set_modify_time($modify_time, true);

		$object->set___loaded_fields($loaded_fields, false);
		return $object->set_loaded(true);
	}

	function save($object)
	{
		$base = $object->storage_base_dir();
		$pfx  = $object->storage_file_prefix();

		if(empty($base))
		{
			$url_data = url_parse($object->id());
			$base = secure_path(config('page.fs.separate.base_dir', BORS_SITE.'/data/fs-separate/').$url_data['path']);
		}

		$success = true;
		foreach($object->changed_fields as $field => $dummy)
		{
			//TODO: Заглушка для скипания левых полей редактора.
			if(!in_array($field, explode(' ', 'cr_type create_time description last_editor_id modify_time nav_name owner_id parents source title')))
				continue;

			$data = $object->$field();
			if(is_array($data))
			{
				$file = secure_path("$base/{$pfx}[{$field}].txt");
				$data = join("\n", $data);
			}
			else
				$file = secure_path("{$base}/{$pfx}{$field}.txt");

			mkpath(dirname($file), 0777);
			if($data)
			{
				@file_put_contents($file, $data);
				@chmod($file, 0666);
			}
			else
				unlink($file);
		}

		return $success;
	}

	function delete($object)
	{
		$base = $object->storage_base_dir();
		$pfx  = $object->storage_file_prefix();
		$d = dir($base);
		while(false !== ($entry = $d->read()))
			if(preg_match("!".preg_quote($pfx)."(\[\w+\]|\w+)\.txt$!", $entry, $m))
				@unlink(secure_path($base.'/'.$entry));
		do
		{
			@rmdir($base);
		} while(($base = dirname($base)) && $base != '/');
	}

	static function find_all($where)
	{
		if($found = self::__find_all('.', $where, bors()->server()->document_root(), 0))
			return $found;

		foreach(bors_dirs() as $base)
			if($base && is_dir($base) && ($found = self::__find_all('', $where, $base, 0)))
				return $found;

		return array();
	}

	private static function __find_all($prefix, $where, $base_dir, $level)
	{
		$found = array();

		// $where пока игнорируется, тупо выполняется поиск всех файлов.
		// Используется атрибут '*depth' - максимальная глубина просмотра
		$dh = dir($base_dir);
		if(!$dh)
			return $found;

		while(($entry = $dh->read()) !== false)
		{
			$subdir = "$base_dir/$entry";
//			echo "Test $subdir\n";
			if(!is_dir($subdir) || $entry{0} == '.')
				continue;

			if(file_exists("$subdir/{$prefix}title.txt"))
			{
				// Если там есть [.]title.txt - то это страница
				$object = new page_fs_separate(NULL);
				self::__load_object($object, $subdir, $prefix);
				if($object->loaded())
					$found[] = $object;
			}
			else
			{
				if($level < defval($where, '*depth', 5))
					$found = array_merge($found, self::__find_all($prefix, $where, $subdir, $level+1));
			}
		}

		return $found;
	}

	private static function __load_object(&$object, $dir, $pfx)
	{
//		echo "Load object from $dir\n";

		$object->set_html_disable(false, false);
		$object->set_lcml_tags_enabled(NULL, false);

		$object->set_attr('storage_base_dir', $dir, false);
		$object->set_attr('storage_file_prefix', $pfx, false);
		$create_time = time()+99999;
		$modify_time = 0;

		$d = dir($dir);
		$loaded_fields = array();
		while(false !== ($entry = $d->read()))
		{
			//FIXME: посмотреть, не нужно ли добавить '!' в preg_quote()
			if(preg_match("!".preg_quote($pfx)."\[(\w+)\]\.txt$!", $entry, $m))
			{
				$data = array();
				foreach(file("{$dir}/{$entry}") as $s)
					$data[] = $object->cs_f2i($s);

				if(method_exists($object, $method = "set_{$m[1]}"))
					$object->$method($data, false);
				else
					$object->set($m[1], $data, false);

				$loaded_fields[$m[1]] = $data;
			}
			elseif(preg_match("!".preg_quote($pfx)."(\w+)\.txt$!", $entry, $m))
			{
				$data = $object->cs_f2i(file_get_contents("{$dir}/{$entry}"));
				if(method_exists($object, $method = "set_{$m[1]}"))
					$object->$method($data, false);
				else
					$object->set($m[1], $data, false);

				if($m[1] == 'create_time')
					$create_time = -1;
				elseif($m[1] == 'modify_time')
					$modify_time = -1;
				elseif($m[1] == 'title' || $m[1] == 'source')
				{
					if($create_time != -1)
						$create_time = min($create_time, filectime("{$dir}/{$entry}"));
					if($modify_time != -1)
						$modify_time = max($modify_time, filemtime("{$dir}/{$entry}"));
				}

				$loaded_fields[$m[1]] = $data;
			}
		}
		$d->close();

		if($create_time > 0)
			$object->set_create_time($create_time, true);
		if($modify_time > 0)
			$object->set_modify_time($modify_time, true);

		$object->set___loaded_fields($loaded_fields, false);
		return $object->set_loaded(true);
	}
}
