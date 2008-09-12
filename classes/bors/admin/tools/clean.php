<?php

class bors_admin_tools_clean extends base_page
{
	function config_class() { return config('admin_config_class'); }

	function parents() { return array($this->id()); }
	
	function title() { return ec('очистка кеша'); }

	function object() { return object_load(urldecode($this->id())); }

	function pre_show()
	{
		$obj = $this->object();
		if(!$obj)
			return bors_message(ec('Не найден объект ').$this->id());
		
		$obj->cache_clean_self();
		return go($this->object()->url($this->object()->page()));
	}
}
