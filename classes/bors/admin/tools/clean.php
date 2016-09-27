<?php

class bors_admin_tools_clean extends bors_page
{
//	function config_class() { return config('admin_config_class'); }

	function parents() { return array($this->id()); }

	function title() { return ec('очистка кеша'); }

	//TODO: временный костыль, чтобы не грузился при повторных запросах объекта этот же самый класс.
	function object() { return object_load(urldecode($this->id()).'?'); }

	function pre_show()
	{
		$obj = $this->object();
		if(!$obj)
			return bors_message(ec('Не найден объект ').$this->id());

		bors()->set_main_object($obj, true);

		if($this->page() == 1)
			$obj->cache_clean_self();
		else
			echo $obj->recalculate_full();

		$r = $obj->pre_show();
		if($r === true)
			return $r;

		echo $obj->content();

		return true; // go($this->object()->url_ex($this->object()->page()));
	}
}
