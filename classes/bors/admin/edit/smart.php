<?php

class bors_admin_edit_smart extends base_page
{
	function config_class() { return config('admin_config_class'); }

	function parents()
	{
		if(!($obj = $this->object()))
			return array();

		if(($adm = $obj->admin_parent_url()) && $adm != $this->url())
			return array($adm);

		return array($obj->url());
	}

	function title() { return ec('редактор'); }

	function object()
	{
		$id = urldecode($this->id());
		if(preg_match('!^/!', $id))
			$id = 'http://'.$_SERVER['HTTP_HOST'].$id;

		return object_load($id); 
	}

	function fields() { return explode(',', $this->args('fields')); }

	function pre_parse()
	{
		if(!($me = bors()->user()) && !config('admin_can_nologin'))
			return bors_message(ec('Вы не авторизованы'));
	}

	function local_data()
	{
		templates_noindex();

		return array(
			'object' => $this->object(),
			'fields' => $this->fields(),
			'referer' => ($ref = bors()->referer()) ? $ref : 'newpage_admin',
		);
	}

//	function url() { return '/admin/edit-smart/?object='.$this->object()->internal_uri(); }
	function admin() { return $this->object()->admin(); }
}
