<?php

class bors_admin_edit extends bors_page
{
	function config_class() { return config('admin_config_class'); }

	function title()
	{
		if(!$this->id())
			return ec('Добавить ') . bors_lower($this->main_class_title_vp());

		return ec('Редактируется ') . bors_lower($this->admin_object()->class_title()) . ec(' «') . $this->real_object()->title().ec('»');
	}

	function main_class_title_vp()
	{
		$cn = $this->main_class();
		$tc = new $cn;
		return call_user_func(array($tc, 'class_title_vp'));
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

	function admin_object()
	{
		if($this->__havefc())
			return $this->__lastc();

		$admin_main_class = $this->main_class();

		return $this->__setc(object_load($admin_main_class, $this->id()));
	}

	function real_object()
	{
		if($this->__havefc())
			return $this->__lastc();

		$admin_main_class = $this->main_class();
		$real_main_class = call_user_func(array($admin_main_class, 'extends_class'));

		return $this->__setc(object_load($real_main_class, $this->id()));
	}

	function template_local_vars() { return parent::template_local_vars().($this->id() ? ' admin_object real_object' : ''); }
}
