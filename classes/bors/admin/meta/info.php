<?php

class bors_admin_meta_info extends bors_page
{
	function config_class() { return config('admin_config_class'); }

	function nav_name() { return $this->target()->nav_name(); }
	function title()
	{
		return ec('Информация о ').call_user_func(array($this->main_class(), 'class_title_pp'));
	}

	function target() { return $this->id() ? bors_load($this->main_class(), $this->id()) : NULL; }

	function item_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->main_class());
	}

	function body_data()
	{
		return array_merge(parent::body_data(), array(
			$this->item_name() => $this->target(),
		));
	}
}
