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

/*		foreach(get_object_vars($object) as $field => $value)
		{
			if(!preg_match('!^stb_(.+)$!', $field, $m))
				continue;
					
			$name	= $m[1];
			$set	= "set_{$name}";

			if(file_exists($file = "{$dir}/.{$name}.txt"))
				$object->$set(ec(file_get_contents($file)), false);
			elseif(file_exists($file = "{$dir}/.[{$name}].txt"))//TODO: сейчас без конвертации!
				$object->$set(file($file), false);
		}
*/
		return true;
	}
	
	function save($object)
	{
		debug_exit("Try to save file separated format");
	}
}
