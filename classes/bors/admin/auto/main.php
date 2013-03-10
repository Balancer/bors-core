<?php

class bors_admin_auto_main extends bors_admin_meta_main
{
	function can_be_empty() { return false; }
	function is_loaded() { return (bool) $this->main_admin_class(); }

	function main_admin_class()
	{
		$rel = bors_unplural(str_replace('/', '_', trim($this->id(), '/')));
		$test = config('admin_auto_class_base', config('project.name').'_admin').'_'.$rel;
		if(class_include($test))
			return $test;

		return $this->main_class();
	}

	function main_class()
	{
		$rel = bors_unplural(str_replace('/', '_', trim($this->id(), '/')));
		$test = config('classes_auto_base', config('project.name')).'_'.$rel;
		if(class_include($test))
			return $test;

		return NULL;
	}
}
