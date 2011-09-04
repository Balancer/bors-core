<?php

class bors_admin_meta_search extends bors_admin_page
{
	function config_class() { return config('admin_config_class'); }

	function title() { return ec('Поиск'); }
	function nav_name() { return ec('поиск'); }
	function is_auto_url_mapped_class() { return true; }

	function main_class() { bors_throw(ec('Не определён класс для поиска')); }

	function body_data()
	{
		$data = array();

		if(empty($_GET['q']))
			return $data;

		$data['items'] = bors_find_all($this->main_class(), array(
			'where' => $this->where(),
			'page' => $this->page(),
			'per_page' => $this->items_per_page(),
			'order' => $this->order()));

		$data['query'] = trim(urldecode(@$_GET['q']));

		return $data;
	}

	function order() { return 'title'; }

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
		$properties = explode(' ', bors_lib_object::get_static($this->main_class(), 'admin_searchable_properties'));

		foreach($properties as $p)
		{
			bors_lib_orm::field($this->main_class(), $p);
			$qq[] = "`{$p['name']}` LIKE {$q}";
		}

		return array('('.join(' OR ', $qq).')');
	}

	function append_template() { return 'xfile:main.html'; }
}
