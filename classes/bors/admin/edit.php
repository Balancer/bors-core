<?php

class bors_admin_edit extends bors_page
{
	function config_class() { return config('admin_config_class'); }

	function title()
	{
		if(!$this->id())
			return ec('Добавить ') . bors_lower($this->admin_object()->class_title_vp());

		return ec('Редактируется ') . bors_lower($this->admin_object()->class_title()) . ec(' «') . $this->real_object()->title().ec('»');
	}

	function nav_name()
	{
		return $this->id() ? ec('редактор') : ec('добавить');
	}
/*
	private $_object = false;
	function object()
	{
		if($this->_object !== false)
			return $this->_object;

		return $this->_object = object_load($this->main_class(), $this->id());
	}
*/
	function auto_objects()
	{
		$admin_main_class = $this->main_class();
		$real_main_class = call_user_func(array($admin_main_class, 'extends_class'));
		return array(
			'admin_object' => "$admin_main_class(id)",
			'real_object' => "$real_main_class(id)",
		);
	}

	function template_local_vars() { return parent::template_local_vars().($this->id() ? ' admin_object real_object' : ''); }
}
