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
			$pfx = '\.';
		else
		{
			foreach(bors_dirs() as $base)
			{
				if(file_exists(($dir = "{$base}/data/fs-separate{$path}").'title.txt'))
				{
					$found = true;
					break;
				}
			}
		}

		if(!$found)
			return $object->set_loaded(false);

		// По дефолту в separate разрешён HTML и все BB-тэги.
		$object->set_html_disable(false, false);
		$object->set_lcml_tags_enabled(NULL, false);

		$object->set_storage_base_dir($dir, false);
		$object->set_storage_file_prefix($pfx, false);
		$create_time = time();
		$modify_time = 0;

		$d = dir($dir);
		while(false !== ($entry = $d->read()))
		{
			if(preg_match("!$pfx\[(\w+)\]\.txt$!", $entry, $m))
			{
				$data = array();
				foreach(file("{$dir}/{$entry}") as $s)
					$data[] = $object->cs_f2i($s);

				if(method_exists($object, $method = "set_{$m[1]}"))
					$object->$method( $data, false);
				else
					$object->set($m[1], $data, false);
			}
			elseif(preg_match("!$pfx(\w+)\.txt$!", $entry, $m))
			{
				$data = $object->cs_f2i(file_get_contents("{$dir}/{$entry}"));
				if(method_exists($object, $method = "set_{$m[1]}"))
					$object->$method($data, false);
				else
					$object->set($m[1], $data, false);
				if($m[1] == 'title' || $m[1] == 'source')
				{
					$create_time = min($create_time, filectime("{$dir}/{$entry}"));
					$modify_time = max($modify_time, filemtime("{$dir}/{$entry}"));
				}
				if($m[1] == 'create_time')
					$create_time = 0;
				if($m[1] == 'modify_time')
					$modify_time = time()+99999;
			}
		}
		$d->close();

		if($create_time)
			$object->set_create_time($create_time, true);
		if($modify_time <= time())
			$object->set_modify_time($modify_time, true);

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

		$skip_fields = explode(' ', $object->storage_skip_fields());

		$success = true;
		foreach($object->changed_fields as $field_name => $field_property)
		{
			if(in_array($field_name, $skip_fields))
				continue;

			$data = $object->$field_property;
			if(is_array($data))
			{
				$file = secure_path("$base/{$pfx}[{$field_name}].txt");
				$data = join("\n", $data);
			}
			else
				$file = secure_path("$base/$pfx$field_name.txt");

			mkpath(dirname($file), 0777);
			@file_put_contents($file, $data);
			@chmod($file, 0666);
		}

		return $success;
	}
}
