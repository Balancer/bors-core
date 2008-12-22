<?php

class bors_tools_delete extends base_page
{
	function config_class() { return config('admin_config_class'); }

	function parents()
	{
		$obj_admin = $this->object()->admin_url();
		return $obj_admin ? array($obj_admin) : array($this->object()->internal_uri());
	}

	function title() { return $this->object()->class_title() . ec(': подтверждение удаления'); }
	function nav_name() { return ec('удаление'); }

	function object() { return object_load($this->id()); }

	function on_action_delete()
	{
		$obj = $this->object();
		if(!$obj)
			return bors_message(ec('Не найден объект ').$this->id());
		
		$obj->delete();
		return go($_GET['ref']);
	}

	function ref()
	{
		if(!empty($_GET['ref']))
			return $_GET['ref'];
			
		return @$_SERVER['HTTP_REFERER'];
	}

	function access_section() { return $this->object()->access_section(); }
}
