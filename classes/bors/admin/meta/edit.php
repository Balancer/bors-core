<?php

class bors_admin_meta_edit extends bors_page
{
	function config_class() { return config('admin_config_class'); }

	function nav_name() { return $this->id() ? $this->news()->nav_name() : ec('добавление'); }
	function title()
	{
		return $this->id() ?
			ec('Редактирование ').call_user_func(array($this->main_class(), 'class_title_rp'))
			: ec('Добавление ').call_user_func(array($this->main_class(), 'class_title_rp'))
		;
	}

	function target_fields()
	{
		
	}
}
