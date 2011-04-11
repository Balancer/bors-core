<?php

class bors_lib_files
{
	static function find($dir, $mask=NULL, $recursive = false, $max_level = 20)
	{
		$files = array();
		if(!is_dir($dir))
			return NULL;

		if(!($dh = opendir($dir)))
			return NULL;

		while(($item = readdir($dh)) !== false)
		{
			if(is_dir($dir.'/'.$item))
			{
				if($recursive && $max_level > 0 && $item != '.' && $item != '..')
				{
					if($sub_files = self::find($dir.'/'.$item, $mask, $max_level - 1))
						foreach($sub_files as $sf)
							$files[] = $item.'/'.$sf;
				}
				continue;
			}

			if($mask)
			{
				if(preg_match("!{$mask}!", $item))
					$files[] = $item;
				continue;
			}

			$files[] = $item;
		}

		closedir($dh);
		sort($files);
		return $files;
	}
}
