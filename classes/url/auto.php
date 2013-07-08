<?php

class url_auto extends url_base
{
	function url_ex($page)
	{
		$obj = $this->id();

		// aviaport_directory_airline -> directory_airline
		$rel_class_name = str_replace(config('classes_auto_base'), '', $obj->class_name());
		// directory_airline -> /directory/airlines/
		$path = str_replace('_', '/', bors_plural($rel_class_name)).'/';

		$path .= $obj->id().'/';

		if($page && ($page != $obj->default_page()))
			$path .= '/'.$page.'.html';

		return $path;
	}
}
