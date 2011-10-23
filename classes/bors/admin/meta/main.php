<?php

class bors_admin_meta_main extends bors_admin_paginated
{
	function config_class() { return config('admin_config_class'); }
	function access_name() { return bors_lib_object::get_static($this->main_admin_class(), 'access_name'); }

	function title() { return ec('Управление ').bors_lib_object::get_static($this->main_class(), 'class_title_tpm'); }
	function nav_name() { return bors_lib_object::get_static($this->main_class(), 'class_title_m'); }

	function main_class() { bors_throw(ec('Не определён главный класс')); }
	function main_admin_class() { return $this->main_class(); }

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
