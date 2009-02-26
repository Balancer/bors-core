<?php

class bors_admin_edit extends base_page
{
	function config_class() { return config('admin_config_class'); }

	function title()
	{
		if(!$this->id())
			return ec('Добавить ') . $this->object()->class_title_rp();


		return ec('Редактируется ') . $this->object()->class_title_rp() . $this->object()->title();
	}

	function nav_name()
	{
		return $this->id() ? ec('редактор') : ec('новое');
	}

	private $_object = false;
	function object()
	{
		if($this->_object !== false)
			return $this->_object;

		return $this->_object = object_load($this->main_class(), $this->id());
	}
}
