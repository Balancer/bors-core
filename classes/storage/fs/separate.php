<?php

class storage_fs_separate extends base_null
{
	function load($object)
	{
//		if(!$object->id() || is_object($object->id()))
//			return;
		
		$dir = $object->dir();
		
		if(!file_exists("{$dir}/.title.txt"))
			return false;

		$d = dir($dir);
		while (false !== ($entry = $d->read()))
		{
			if(preg_match("!\.\[(\w+)\]\.txt$!", $entry, $m) && method_exists($object, $method = "set_{$m[1]}"))
			{
				$data = array();
				foreach(file("{$dir}/{$entry}") as $s)
					$data[] = ec($s);
				$object->$method($data, false);
			}
			elseif(preg_match("!\.(\w+)\.txt$!", $entry, $m) && method_exists($object, $method = "set_{$m[1]}"))
				$object->$method(ec(file_get_contents("{$dir}/{$entry}")), false);
		}
		$d->close();

		return true;
	}
	
	function save($object)
	{
		debug_exit("Try to save file separated format");
	}
}
