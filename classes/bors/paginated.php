<?php

class bors_paginated extends base_page_paged
{
	function foo_object() { $class_name = $this->main_class(); return new $class_name(NULL); }

	function item_fields()
	{
		$class_name = $this->main_class();
		$foo = new $class_name(NULL);

		if($this->args('is_admin_list') || $this->get('is_admin_list'))
			$data = $foo->get('item_list_admin_fields');

		if(empty($data))
			$data = $foo->get('item_list_fields');

		if($data)
			return $data;

		return array(
			'mtime' => ec('Дата изменения'),
			'title' => ec('Название'),
			'id' => ec('ID'),
		);
	}

	function where()
	{
		$where = parent::where();

		$join_class = $this->get('inner_join_filter');
		$join_type = 'inner';
		if(!$join_class)
		{
			$join_class = $this->get('join_counter_class');
			$join_type = 'left';
		}

		if($join_class)
		{
			$db_name = bors_lib_orm::db_name($join_class);
			$table_name = bors_lib_orm::table_name($join_class);

			if(preg_match('/^(\w+)\((\w+)\)$/', $join_class, $m))
			{
				$inner_field = $m[2];
				$inner_join_class = $m[1];
			}
			else
				$inner_field = bors_core_object_defaults::item_name($this->main_class()).'_id';

			if($this->get('counts_in_list'))
			{
				if($join_type == 'inner')
				{
					$where[$join_type.'_join'] = "`$db_name`.`$table_name` ON ({$this->main_class()}.id = $inner_field)";
					$where['group'] = $inner_field;
					$where['*set'] = 'COUNT(*) AS `group_count`';
				}
				else
					$where['*set'] = "'$join_class' AS `b_counter_class`";
			}
			else
				$where[] = "$inner_field IN (SELECT $inner_field FROM `$db_name`.`$table_name`)";

		}

		return $where;
	}
}
