<?php

class bors_page_meta_main extends bors_paginated
{
	function _config_class_def() { return config('page_config_class'); }

	function _title_def() { return bors_ucfirst($this->foo_object()->class_title_m()); }
	function _nav_name_def() { return bors_lower($this->foo_object()->class_title_m()); }

	function _main_class_def()
	{
		$class_name = str_replace('_main', '', $this->class_name());
		return bors_unplural($class_name);
	}

	function body_data()
	{
		return array_merge(parent::body_data(), array(
			'item_fields' => $this->foo_object()->item_fields(),
		));
	}
}
