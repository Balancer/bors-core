<?php

//	http://admin.aviaport.ru/digest/origins/search/?q=%D0%B4%D0%BE%D0%BC%D0%BE
//	http://admin.aviaport.wrk.ru/directory/aviation/search/
//	http://admin2.aviaport.wrk.ru/newses/stories/search/?q=253

class bors_admin_meta_search extends bors_admin_meta_main
{
	function admin_search_url() { return $this->url(); }

	function title() { return ec('Поиск по ').$this->foo_object()->class_title_dpm(); }
	function nav_name() { return ec('поиск'); }
	function auto_map() { return true; }

	function main_class() { bors_throw(ec('Не определён класс для поиска')); }

	function q() { return trim(urldecode(defval($_GET, 'q', ''))); }
	function w() { return trim(urldecode(defval($_GET, 'w', ''))); }

	function parents()
	{
		if($this->q())
			return array(dirname(dirname($this->url())).'/');

		return parent::parents();
	}

	function body_data()
	{
		$data = parent::body_data();
		$data['w'] = $this->w();

		if(empty($_GET['q']))
			return $data;

		$where = $this->where();
		$where['page'] = $this->page();
		$where['per_page'] = $this->items_per_page();
		$where['order'] = $this->order();

		$data['items'] = bors_find_all($this->main_admin_class(), $where);

		$data['query'] = trim(urldecode(@$_GET['q']));

		$main_class = $this->main_class();
		$foo = new $main_class(NULL);

		$fields = $this->get('item_fields');
		if(!$fields)
			$fields = bors_lib_object::get_foo($this->main_class(), 'item_list_admin_fields');

		$data['item_fields'] = $fields;

		$data['admin_search_url'] =  $this->get('admin_search_url');

		return $data;
	}

	function _order_def() { return 'title'; }

	function total_items()
	{
		return bors_count($this->main_class(), $this->where());
	}

	function where()
	{
		$where = parent::where();

		if(empty($_GET['q']))
			return $where;

		$q = "'%".addslashes(trim(urldecode($_GET['q'])))."%'";

		$any = @$_GET['w'] == 'a';

		$qq = array();

		$main_admin_class = $this->main_admin_class();
		$foo = new $main_admin_class(NULL);

		$properties = $foo->admin_searchable_title_properties();
		$all_properties   = $foo->admin_searchable_properties();

		if(!is_array($properties))
			$properties = explode(' ', $properties);

		if(!is_array($all_properties))
			$all_properties = explode(' ', $all_properties);

		if($any)
			$properties += $all_properties;

		foreach($properties as $p)
		{
			if(strpos($p, '`') === false)
			{
				$x = bors_lib_orm::parse_property($main_admin_class, $p);
				if(!empty($x['name']))
					$field = "`{$x['name']}`";
			}
			else
				$field = $p;

			if(!empty($field))
				$qq[] = "$field LIKE {$q}";
		}

		if(empty($qq))
			bors_throw(ec('Не заданы поля для поиска (admin_searchable_title_properties/admin_searchable_properties) класса ').$main_admin_class);

		$where[] = '('.join(' OR ', $qq).')';

		return $where;
	}

	function append_template() { return 'xfile:main.html'; }
}
