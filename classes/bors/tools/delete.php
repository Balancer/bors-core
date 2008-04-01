<?php

class bors_tools_delete extends base_page
{
	function config_class() { return config('admin_config_class'); }

	function parents() { return array($this->id()); }
	
	function title() { return ec('удаление'); }

	function object() { return object_load($this->id()); }

	function on_action_delete()
	{
		$obj = $this->object();
		if(!$obj)
			return bors_message(ec('Не найден объект ').$this->id());
		
		$obj->delete();
		return go($_GET['ref']);
	}
}
