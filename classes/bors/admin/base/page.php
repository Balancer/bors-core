<?php

// Прототип страниц администрирования объектов-страниц сайта.

class bors_admin_base_page extends bors_admin_page
{
	function config_class() { return config('admin_config_class'); }
	function parents() { return array($this->object()->url()); }

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

	function admin() { return $this->object()->admin(); }
}
