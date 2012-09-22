<?php

class bors_admin_property extends bors_admin_page
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

	function pre_parse()
	{
		if(!($me = bors()->user()) && !config('admin_can_nologin'))
			return bors_message(ec('Вы не авторизованы'));

		if(!$me->can_edit($this->object()))
			return bors_message(ec('Вы не можете редактировать этот объект'));

		return false;
	}

	function local_data()
	{
		template_noindex();

		return array(
			'object' => $this->object(),
			'referer' => ($ref = bors()->referer()) ? $ref : 'newpage_admin',
		);
	}

	function admin() { return $this->object()->admin(); }
}
