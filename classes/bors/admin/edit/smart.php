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
		template_noindex();

		$fields = array();
		$args = array();
		foreach($this->object()->editor_fields_list() as $field_title => $x)
		{
			$data = array();
			$field_name = $x;

			$type = 'input';
			if(preg_match('/^(\w+)\|(.+)$/', $field_name, $m)) // source|textarea=4
			{
				$field_name = $m[1];
				$type = $m[2];
			}

			if(preg_match('/^(\w+)\((.+)\)$/', $type, $m))
			{
				$type = $m[1];
				foreach(explode(',', $m[2]) as $pair)
					if(preg_match('/^(\w+)=(.+)$/', $pair, $mm))
						$args[$mm[1]] = $mm[2];
			}

			if(preg_match('/^(\w+)=(.+)$/', $type, $m))
			{
				$type = $m[1];
				switch($type)
				{
					case 'dropdown':
						$data['named_list'] = $m[2];
						break;
				}
			}

			if(empty($type))
				$type = 'input';

			$fields[$field_title] = array_merge($data, array('title' => $field_title, 'name' => $field_name, 'origin' => $x, 'type' => $type, 'args' => $args));
		}

		if(!$fields)
			$fields = bors_lib_orm::all_fields($this->object());

//		var_dump($fields);

		return array(
			'object' => $this->object(),
//			'fields' => $fields,
//			'fields' => $this->fields_map_db(),
			'referer' => ($ref = bors()->referer()) ? $ref : 'newpage_admin',
			'auto_fields' => $fields,
		);
	}

//	function url() { return '/admin/edit-smart/?object='.$this->object()->internal_uri(); }
	function admin() { return $this->object()->admin(); }
}
