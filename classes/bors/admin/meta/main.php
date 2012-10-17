<?php

class bors_admin_meta_main extends bors_admin_paginated
{
	function _config_class_def() { return config('admin_config_class'); }
	function _access_name_def() { return bors_lib_object::get_static($this->main_admin_class(), 'access_name'); }

	function _title_def() { return ec('Управление ').bors_lib_object::get_foo($this->main_class(), 'class_title_tpm'); }
	function _nav_name_def() { return bors_lib_object::get_foo($this->main_class(), 'class_title_m'); }

	function _main_class_def()
	{
		$class_name = str_replace('_admin_', '_', $this->class_name());
		$class_name = str_replace('_main', '', $class_name);
		return bors_unplural($class_name);
	}

	function _main_admin_class_def()
	{
		$class_name = str_replace('_main', '', $this->class_name());
		$admin_class_name = bors_unplural($class_name);
		if(class_include($admin_class_name))
			return $admin_class_name;

		return $this->main_class();
	}

	function body_data()
	{
		$new_link_title = false;
		if(!$this->get('skip_auto_admin_new'))
			if(!bors_lib_object::get_foo($this->main_class(), 'skip_auto_admin_new'))
				$new_link_title = bors_lib_object::get_foo($this->main_class(), 'class_title_vp');

		$fields = $this->get('item_fields');
		if(!$fields)
			$fields = bors_lib_object::get_foo($this->main_class(), 'item_list_admin_fields');

		return array_merge(parent::body_data(), array(
			'new_link_title' => $new_link_title,
			'item_fields' => $fields,
			'admin_search_url' => $this->page() > 2 ? false : $this->get('admin_search_url'),
		));
	}

	function _order_def()
	{
		if($current_sort = bors()->request()->data_parse('signed_names', 'sort'))
			return $current_sort;

		return parent::_order_def();
	}
}
