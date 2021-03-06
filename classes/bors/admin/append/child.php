<?php

class bors_admin_append_child extends bors_admin_page
{
	function config_class() { return config('admin_config_class'); }
	function parents() { return $this->object() ? array($this->object()->url()) : array(); }
	function title() { return ec('новая дочерняя страница'); }

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

	function body_data()
	{
		template_noindex();

		$base = $this->object()->url();
		$idx = 1;
		while(object_load($sub = "{$base}new_{$idx}/") && $idx < 100)
			$idx++;

		return array(
			'object' => $this->object(),
			'sub_url' => $sub,
			'referer' => ($ref = bors()->referer()) ? $ref : 'newpage_admin',
		);
	}

	function admin() { return $this->object()->admin(); }

	function on_action($data)
	{
		$data['id'] = $data['new_url'];
		$data['main_url'] = $data['new_url'];
		unset($data['new_url']);
		$new = object_new_instance($data['new_object_class'], $data, true, true);
		return $new ? go($new->url()) : true;
	}
}
