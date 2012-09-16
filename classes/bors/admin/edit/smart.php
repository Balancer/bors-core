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

	function title() { return ec('Редактор ').($this->object()->class_title_rp()).ec(' «').($this->object()->title()).ec('»'); }
	function nav_name() { return $this->object() ? $this->object()->nav_name() : ec('редактор'); }

	function can_be_empty() { return false; }
	function loaded() { return (bool)$this->object(); }

	function object()
	{
		$id = urldecode($this->id());
		if(preg_match('!^/!', $id))
			$id = 'http://'.$_SERVER['HTTP_HOST'].$id;

		return bors_load_uri($id);
	}

	function fields() { return explode(',', $this->args('fields')); }

	function access() { return $this->object()->access(); }

	function pre_parse()
	{
		$object = $this->object();
		if(!$object->access()->can_edit())
		{
			bors_message(ec('Вы не можете редактировать ')
				.$object->class_title_vp()
				.ec(" «").$object->title().ec("»")
				."\n<!-- access={$object->access()} -->"
			);
			return true;
		}

/*				bors_throw(
*/

//		if(!($me = bors()->user()) && !config('admin_can_nologin'))
//			return bors_message(ec('Вы не авторизованы'));

		return parent::pre_parse();
	}

	function body_data()
	{
		template_noindex();

		$fields = array();
		$args = array();

		if(method_exists($this->object(), 'editor_fields_list'))
			foreach($this->object()->editor_fields_list() as $field_title => $x)
		{
			if(is_array($x))
			{
				$property_name = defval($x, 'property');
				$data = $x;
				$data = array_merge(bors_lib_orm::parse_property($this->object()->class_name(), $property_name), $data);
				$type = defval($data, 'type');
			}
			else
			{
				$property_name = $x;
				$data = array();
				if(preg_match('/^\w+$/', $property_name))
				{
					$data = bors_lib_orm::parse_property($this->object()->class_name(), $property_name);
//					print_dd($data);
					$type = defval($data, 'type');
				}
				else
				{
					$property_name = $x;
					$type = 'input';
					if(preg_match('/^(\w+)\|(.+)$/', $property_name, $m)) // source|textarea=4
					{
						$property_name = $m[1];
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
							case 'image':
								$data['geometry'] = $m[2];
								break;
							case 'textarea':
							case 'bbcode':
								$data['rows'] = $m[2];
							break;
						}
					}
				}
			}

			if(empty($type))
				$type = 'input';

			$fields[$field_title] = array_merge($data, array('title' => $field_title, 'name' => $property_name, 'origin' => $x, 'type' => $type, 'args' => $args));
		}

		if(!$fields)
			$fields = bors_lib_orm::all_fields($this->object());

		return array_merge(parent::body_data(), array(
			'object' => $this->object(),
//			'fields' => $fields,
//			'fields' => $this->fields(),
			'referer' => ($ref = bors()->referer()) ? $ref : 'newpage_admin',
			'auto_fields' => $fields,
			'items' => object_property($this->object(), 'edit_smart_items_append'),
			'cross' => object_property($this->object(), 'cross_links'),
		));
	}

//	function url() { return '/admin/edit-smart/?object='.$this->object()->internal_uri(); }
	function admin() { return $this->object()->admin(); }

//	function template() { return 
}
