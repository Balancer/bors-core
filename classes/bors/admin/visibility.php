<?php

class bors_admin_visibility extends bors_admin_page
{
	function config_class() { return config('admin_config_class'); }
	function parents() { return $this->object() ? array($this->object()->url()) : array(); }
	function title() { return ec('Свойства ').bors_lower($this->object()->class_title_rp()).ec(' «').$this->object()->title().ec('»'); }
	function nav_name() { return ec('свойства'); }

	function object()
	{
		$id = urldecode($this->id());
		if(preg_match('!^/!', $id))
			$id = 'http://'.$_SERVER['HTTP_HOST'].$id;

		return object_load($id); 
	}

	function on_action_hide($data)
	{
		$this->object()->set_is_hidden(1, true);
		return go_ref($this->object()->admin_url());
	}

	function on_action_show($data)
	{
		$this->object()->set_is_hidden(0, true);
		return go_ref($this->object()->admin_url());
	}
}
