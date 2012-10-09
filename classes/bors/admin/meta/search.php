<?php

class bors_admin_meta_search extends bors_admin_meta_main
{
	function config_class() { return config('admin_config_class'); }

	function admin_search_url() { return $this->url(); }

	function title() { return ec('Поиск'); }
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

		$properties = explode(' ', $foo->admin_searchable_properties());

		foreach($properties as $p)
		{
			bors_lib_orm::field($this->main_class(), $p);
			$qq[] = "`{$p['name']}` LIKE {$q}";
		}

		$where = array('('.join(' OR ', $qq).')');
//		var_dump($where);
		return $where;
	}

	function append_template() { return 'xfile:main.html'; }
}
