<?php

class bors_auto_search_result extends bors_auto_search
{
	function _title_def()
	{
		return ec('Результат поиска по ').call_user_func(array($this->main_class(), 'class_title_dpm')).ec(' по запросу «').$this->query().ec('»');
	}

	function _nav_name_def() { return ec('«').$this->query().ec('»'); }

	function _order_def() { return bors_lib_object::get_foo($this->main_class(), 'admin_items_list_order', '-create_time'); }

	function where()
	{
		if(!($q = $this->query()))
			return array();

		$q0 = "'".addslashes(trim($q))."'";
		$q = "'%".addslashes(trim($q))."%'";

		$where = array();

		$class_name = $this->main_class();

		$qq = array();
		$properties = explode(' ', bors_lib_object::get_foo($class_name, 'admin_searchable_properties'));

		foreach($properties as $p)
		{
			if(preg_match('/^(\w+)=$/', $p, $m))
			{
				$x = bors_lib_orm::parse_property($class_name, $m[1]);
				$qq[] = "`{$x['name']}` = {$q0}";
			}
			else
			{
				$x = bors_lib_orm::parse_property($class_name, $p);
				$qq[] = "`{$x['name']}` LIKE {$q}";
			}
		}

		$where[] = '('.join(' OR ', $qq).')';

		return $where;
	}

	function result_fields()
	{
		$class_name = $this->main_class();
		$foo = new $class_name(NULL);
		if($data = $foo->get('search_result_fields'))
			return $data;

		if($data = $foo->get('item_list_fields'))
			return $data;

		return array(
			'title' => ec('Название'),
			'id' => ec('ID'),
		);
	}
}
