<?php

class bors_admin_meta_main extends bors_paginated
{
	function config_class() { return config('admin_config_class'); }
	function access_name() { return bors_lib_object::get_static($this->main_admin_class(), 'access_name'); }

	function title() { return ec('Управление ').bors_lib_object::get_static($this->main_class(), 'class_title_tpm'); }
	function nav_name() { return bors_lib_object::get_static($this->main_class(), 'class_title_m'); }

	function body_data()
	{
		return array_merge(parent::body_data(), array(
			'new_link_title' => bors_lib_object::get_static($this->main_class(), 'class_title_vp'),
		));
	}
}
