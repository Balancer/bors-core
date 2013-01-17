<?php

//	http://admin.aviaport.ru/digest/origins/search/?q=%D0%B4%D0%BE%D0%BC%D0%BE

class bors_admin_meta_search extends bors_admin_meta_main
{
	function admin_search_url() { return $this->url(); }

	function title() { return ec('Поиск по ').$this->foo_object()->class_title_dpm(); }
	function nav_name() { return ec('поиск'); }
	function is_auto_url_mapped_class() { return true; }

	function main_class() { bors_throw(ec('Не определён класс для поиска')); }

	function body_data()
	{
		$data = parent::body_data();

		if(empty($_GET['q']))
			return $data;

		$data['items'] = bors_find_all($this->main_class(), array(
			'where' => $this->where(),
			'page' => $this->page(),
			'per_page' => $this->items_per_page(),
			'order' => $this->order()));

		$data['query'] = trim(urldecode(@$_GET['q']));

		$main_class = $this->main_class();
		$foo = new $main_class(NULL);


		$fields = $this->get('item_fields');
		if(!$fields)
			$fields = bors_lib_object::get_foo($this->main_class(), 'item_list_admin_fields');

		$data['item_fields'] = $fields;

		return $data;
	}

	function _order_def() { return 'title'; }

	function total_items()
	{
		return bors_count($this->main_class(), array('where' => $this->where()));
	}

	function where()
	{
		if(empty($_GET['q']))
			return array();

		$q = "'%".addslashes(trim(urldecode($_GET['q'])))."%'";

		$qq = array();

		$main_class = $this->main_class();
		$foo = new $main_class(NULL);

		$properties = $foo->admin_searchable_properties();
		if(!is_array($properties))
			$properties = explode(' ', $properties);

		foreach($properties as $p)
		{
			if(strpos($p, '`') === false)
			{
				$x = bors_lib_orm::parse_property($this->main_class(), $p);
				$field = "`{$x['name']}`";
			}
			else
				$field = $p;
//			var_dump($this->main_class(), $p, $field);
			$qq[] = "$field LIKE {$q}";
		}

		$where = array('('.join(' OR ', $qq).')');
//		var_dump($where);
		return $where;
	}

	function append_template() { return 'xfile:main.html'; }
}
