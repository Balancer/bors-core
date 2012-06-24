<?php

class bors_paginated extends base_page_paged
{
	function item_fields()
	{
		$class_name = $this->main_class();
		$foo = new $class_name(NULL);
		if($data = $foo->get('item_list_fields'))
			return $data;

		return array(
			'title' => ec('Название'),
			'id' => ec('ID'),
		);
	}

	function where()
	{
		$where = parent::where();

		if($inner_join_class = $this->get('inner_join_filter'))
		{
			$db_name = bors_lib_orm::db_name($inner_join_class);
			$table_name = bors_lib_orm::table_name($inner_join_class);

			if(preg_match('/^(\w+)\((\w+)\)$/', $inner_join_class, $m))
			{
				$inner_field = $m[2];
				$inner_join_class = $m[1];
			}
			else
				$inner_field = bors_core_object_defaults::item_name($this->main_class()).'_id';

			if($this->get('counts_in_list'))
			{
				$where['inner_join'] = "`$db_name`.`$table_name` ON ({$this->main_class()}.id = $inner_field)";
				$where['group'] = $this->main_class().'.id';
				$where['*set'] = 'COUNT(*) AS `group_count`';
			}
			else
				$where[] = $this->main_class().".id IN (SELECT $inner_field FROM `$db_name`.`$table_name`)";
		}

		return $where;
	}
}
