<?php

class bors_admin_append_new extends bors_admin_page
{
	function config_class() { return config('admin_config_class'); }
	function parents() { return array(dirname($this->id()).'/'); }
	function title() { return ec('новая страница'); }

	function pre_parse()
	{
		if(!($me = bors()->user()) && !config('admin_can_nologin'))
			return bors_message(ec('Вы не авторизованы'));

//		if(!$me->can_edit($this->object()))
//			return bors_message(ec('Вы не можете редактировать этот объект'));

		return false;
	}

	function body_data()
	{
		template_noindex();

		return array(
			'new_url' => $this->id(),
			'referer' => ($ref = bors()->referer()) ? $ref : 'newpage_admin',
		);
	}

	function admin() { return false; }

	function on_action($data)
	{
		if(empty($data['new_url']))
			return false;

		$data['id'] = $data['new_url'];
		$data['main_url'] = $data['new_url'];
		unset($data['new_url']);
		$new = object_new_instance($data['new_object_class'], $data, true, true);
		return $new ? go($new->url()) : true;
	}
}
