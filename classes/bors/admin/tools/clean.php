<?php

class bors_admin_tools_clean extends bors_admin_page
{
	function config_class() { return config('admin_config_class'); }

	function parents() { return array($this->id()); }

	function title() { return ec('очистка кеша'); }

	//TODO: временный костыль, чтобы не грузился при повторных запросах объекта этот же самый класс.
	function object() { return object_load(urldecode($this->id()).'?'); }

	function pre_show()
	{
		$obj = $this->object();
		if(!$obj)
			return bors_message(ec('Не найден объект ').$this->id());

		var_dump($obj->page());
		$obj->cache_clean_self();
		exit();
		return go($this->object()->url_ex($this->object()->page()));
	}
}
