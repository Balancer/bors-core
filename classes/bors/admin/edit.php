<?php

class bors_admin_edit extends bors_page
{
	function config_class() { return config('admin_config_class'); }

	function can_be_empty() { return false; }
	function is_loaded() { return ! $this->id() || (bool) $this->real_object(); }

	function title()
	{
		if(!$this->id())
			return ec('Добавить ') . bors_lower($this->main_class_title_vp());

		return ec('Редактируется ') . bors_lower($this->admin_object()->class_title()) . ec(' «') . $this->real_object()->title().ec('»');
	}

	function main_class_title_vp()
	{
		$cn = $this->main_class();
		$tc = new $cn(NULL);
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
		$extends_object = new $admin_main_class(NULL); // Чёрт, нельзя вызывать статически.
		$real_main_class = $extends_object->extends_class_name();

		return $this->__setc(object_load($real_main_class, $this->id()));
	}

	function template_local_vars() { return parent::template_local_vars().($this->id() ? ' admin_object real_object' : ''); }
}
