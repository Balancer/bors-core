<?php

class bors_admin_meta_edit extends bors_page
{
	function config_class() { return config('admin_config_class'); }

	function nav_name() { return $this->id() ? $this->target()->nav_name() : ec('добавление'); }
	function title()
	{
		return $this->id() ?
			ec('Редактирование ').call_user_func(array($this->main_class(), 'class_title_rp'))
			: ec('Добавление ').call_user_func(array($this->main_class(), 'class_title_rp'))
		;
	}

	function target() { return bors_load($this->main_class(), $this->id()); }
	function admin_target() { return bors_load($this->main_admin_class(), $this->id()); }

	function item_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->main_class());
	}

	function body_data()
	{
		return array_merge(parent::body_data(), array(
			$this->item_name() => $this->target(),
		), object_property($this->target(), 'data'));
	}
}
