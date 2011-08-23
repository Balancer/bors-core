<?php

class bors_admin_auto_edit extends bors_admin_meta_edit
{
	function can_be_empty() { return false; }
	function loaded() { return !!$this->main_admin_class(); }

	private $_rel;

	function set_args(&$args)
	{
		$this->_rel = bors_unplural(str_replace('/', '_', trim(popval($args, 'page'), '/')));
		return parent::set_args($args);
	}

	function main_admin_class()
	{
		$test = config('admin_auto_class_base', config('project_prefix').'_admin').'_'.$this->_rel;
		if(class_include($test))
			return $test;

		return $this->main_class();
	}

	function main_class()
	{
		$test = config('classes_auto_base', config('project_prefix')).'_'.$this->_rel;
		if(class_include($test))
			return $test;

		return NULL;
	}
}