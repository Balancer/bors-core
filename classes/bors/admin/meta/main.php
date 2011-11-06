<?php

class bors_admin_meta_main extends bors_admin_paginated
{
	function config_class() { return config('admin_config_class'); }
	function access_name() { return bors_lib_object::get_static($this->main_admin_class(), 'access_name'); }

	function title() { return ec('Управление ').bors_lib_object::get_static($this->main_class(), 'class_title_tpm'); }
	function nav_name() { return bors_lib_object::get_static($this->main_class(), 'class_title_m'); }

	function main_class()
	{
		$class_name = str_replace('_admin_', '_', $this->class_name());
		$class_name = str_replace('_main', '', $class_name);
		return bors_unplural($class_name);
	}

	function main_admin_class()
	{
		$class_name = str_replace('_main', '', $this->class_name());
		$admin_class_name = bors_unplural($class_name);
		if(class_include($admin_class_name))
			return $admin_class_name;

		return $this->main_class();
	}

	function body_data()
	{
		if(bors_lib_object::get_static($this->main_class(), 'skip_auto_admin_new'))
			$new_link_title = false;
		else
			$new_link_title = bors_lib_object::get_static($this->main_class(), 'class_title_vp');

		return array_merge(parent::body_data(), array(
			'new_link_title' => $new_link_title,
		));
	}
}
