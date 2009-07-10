<?php

class bors_admin_edit extends base_page
{
	function config_class() { return config('admin_config_class'); }

	function title()
	{
		if(!$this->id())
			return ec('Добавить ') . bors_lower($this->object()->class_title_rp());

		return ec('Редактируется ') . bors_lower($this->object()->class_title()) . ' ' . $this->object()->title();
	}

	function nav_name()
	{
		return $this->id() ? ec('редактор') : ec('добавить');
	}

	private $_object = false;
	function object()
	{
		if($this->_object !== false)
			return $this->_object;

		return $this->_object = object_load($this->main_class(), $this->id());
	}

	function template_local_vars() { return parent::template_local_vars().($this->id() ? ' object' : ''); }
}
