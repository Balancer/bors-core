<?php

class url_auto extends url_base
{
	function url_ex($page)
	{
		$obj = $this->id();
		$class_name = $obj->class_name();

		$path = NULL;
		if($routers = @$GLOBALS['bors_data']['routers'])
		{
//			var_dump($routers);
			foreach($routers as $base_url => $x)
			{
				$base_class = $x['base_class'];
				if(strpos($class_name, $base_class) === 0)
				{
					$class_base = $base_class.'_';
					$class_name = str_replace($base_class, '', $class_name);
					$path = $base_url . str_replace('_', '/', bors_plural($class_name)) . '/';
//					var_dump($base_class, $base_url, $class_name, $path);
				}
			}
		}

		if(!$path)
		{
			// aviaport_directory_airline -> directory_airline
			$rel_class_name = str_replace(config('classes_auto_base'), '', $class_name);
			// directory_airline -> /directory/airlines/
			$path = str_replace('_', '/', bors_plural($rel_class_name)).'/';
		}

		$path .= $obj->id().'/';

		if($page && ($page != $obj->default_page()))
			$path .= '/'.$page.'.html';

		return $path;
	}
}
