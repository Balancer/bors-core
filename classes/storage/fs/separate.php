<?php

class storage_fs_separate extends base_null
{
	function load($object)
	{
		$dir = $object->dir();
		
		if(!file_exists("{$dir}/.title.txt"))
			return $object->set_loaded(false);

		// По дефолту в separate разрешён HTML и все BB-тэги.
		$object->set_html_disable(false, false);
		$object->set_lcml_tags_enabled(NULL, false);

		$d = dir($dir);
		while (false !== ($entry = $d->read()))
		{
			if(preg_match("!\.\[(\w+)\]\.txt$!", $entry, $m))
			{
				$data = array();
				foreach(file("{$dir}/{$entry}") as $s)
					$data[] = ec($s);
				
				if(method_exists($object, $method = "set_{$m[1]}"))
					$object->$method( $data, false);
				else
					$object->set($m[1], $data, false);
			}
			elseif(preg_match("!\.(\w+)\.txt$!", $entry, $m))
			{
				$data = ec(file_get_contents("{$dir}/{$entry}"));
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
		debug_exit("Try to save file separated format");
	}
}
