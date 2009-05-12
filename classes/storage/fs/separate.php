<?php

class storage_fs_separate extends base_null
{
	function load($object)
	{
		$url_data = url_parse($object->called_url());
		$path = $url_data['path'];
		$dir = $url_data['local_path'];

		if($found = file_exists($dir.'/.title.txt'))
			$pfx = '\.';
		else
		{
			foreach(bors_dirs() as $base)
			{
				if(file_exists(($dir = "{$base}/data/fs-separate{$path}").'title.txt'))
				{
					$pfx = '';
					$found = true;
					break;
				}
			}
		}


		if(!$found)
			return $object->set_loaded(false);

//		echo "base=$dir; pfx=$pfx; found=$found<Br/>\n";

		// По дефолту в separate разрешён HTML и все BB-тэги.
		$object->set_html_disable(false, false);
		$object->set_lcml_tags_enabled(NULL, false);

		$object->set_storage_base_dir($dir, false);
		$object->set_storage_file_prefix($pfx, false);

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
			}
		}
		$d->close();
		return $object->set_loaded(true);
	}

	function save($object)
	{
		echo "bd=".$object->storage_base_dir()."<br/>";
//		echo "pfx={$this->_file_prefix}<br/>";
//		echo "o={$object->id()}<Br/>";
		foreach($object->changed_fields as $field_name => $field_property)
		{
			echo "Set $field_name to {$object->$field_property}<br/>";
		}

		debug_exit("Try to save file separated format");

		return true;
	}
}
