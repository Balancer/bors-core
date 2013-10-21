<?php

class bors_paginated extends base_page_paged
{
	function foo_object() { $class_name = $this->main_class(); return new $class_name(NULL); }

	function __is_admin()
	{
		return $this->args('is_admin_list') || $this->get('is_admin_list');
	}

	function __output_class()
	{
		return $this->__is_admin() ? $this->main_admin_class() : $this->main_class();
	}

	function _item_fields_def()
	{
		$class_name = $this->main_class();
		$foo = new $class_name(NULL);
		$foo->set_attr('container_view_object', $this);

		if($this->__is_admin())
		{
			$admin_foo = bors_foo($this->main_admin_class());
			$admin_foo->set_attr('container_view_object', $this);
			$data = $admin_foo->get('item_list_admin_fields');
		}

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
			if(preg_match('/^(\w+)\((\w+)\)$/', $join_class, $m))
			{
				$inner_field = $m[2];
				$join_class = $m[1];
			}
			else
				$inner_field = bors_core_object_defaults::item_name($this->main_class()).'_id';

			$db_name = bors_lib_orm::db_name($join_class);
			$table_name = bors_lib_orm::table_name($join_class);

			// http://matf.aviaport.ru/companies/countries/
			// http://matf.aviaport.ru/companies/regions/
			// http://admin.aviaport.ru/job/cabinets/

			if($this->get('counts_in_list'))
			{
				if($join_type == 'inner')
				{
					$where[$join_type.'_join'] = "`$db_name`.`$table_name` ON ({$this->main_class()}.id = $inner_field)";
					$where['group'] = $inner_field;
					$where['*set'] = 'COUNT(*) AS `group_count`';
				}
				elseif($join_type == 'left')
				{
					// http://admin2.aviaport.wrk.ru/digest/stories/
					$where[$join_type.'_join'] = "`$db_name`.`$table_name` ON ({$this->main_class()}.id = $inner_field)";
					$where['group'] = $inner_field;
					$where['*set'] = 'COUNT(*) AS `group_count`';

//					Непонятно, где использовалось, так что пока отключено
//					$where['*set'] = "'$join_class' AS `b_counter_class`";
				}
			}
			else
				$where[] = "{$this->main_class()}.id IN (SELECT $inner_field FROM `$db_name`.`$table_name`)";

		}
		else
			$this->set_attr('__no_join', true);

		return $where;
	}

	function make_sortable_th($property, $title)
	{
		if(is_numeric($property))
		{
			$property = $title;
			$x = bors_lib_orm::parse_property($this->main_class(), $property);
			$title = defval($x, 'title', $property);
		}

		$sorts = $this->get('sortable', array());
		if($x = $this->get('_sortable_append', array()))
			$sorts = array_merge($x, $sorts);

		$parsed_sorts = array();

		foreach($sorts as $f => $p)
		{
			if(is_numeric($f))
			{
				$f = $p;
				$x = bors_lib_orm::parse_property($this->main_class(), $f);
				$t = defval($x, 'title', $f);
			}

			$parsed_sorts[$f] = $p;
		}

		$sorts = $parsed_sorts;

		if(!($sort_key = @$sorts[$property]))
			return "<th>$title</th>";

		$current_sort = bors()->request()->data_parse('signed_names', 'sort');
		if(preg_match('/^(.+)\*$/', $sort_key, $m))
		{
			$sort_key = $m[1];
			$is_default = true;
		}
		else
			$is_default = false;

		if($is_default && !$current_sort)
			$current_sort = $sort_key;

		$sort = bors_lib_orm::reverse_sign($sort_key, $current_sort);

		$sign = bors_lib_orm::property_sign($sort);
		if($is_default && $sort_key == $sort)
			$sort = NULL;

		$url = bors()->request()->url();

		$url = bors_lib_urls::replace_query($url, 'sort', $sort);

		bors_lib_orm::property_sign($current_sort, true);
		bors_lib_orm::property_sign($sort_key, true);
		if($current_sort != $sort_key)
			$sort_class = 'sort_ascdesc';
		else
			$sort_class = $sign == '-' ? 'sort_asc' : 'sort_desc';

		return "<th class=\"$sort_class\"><a href=\"{$url}\">$title</a></th>";
	}
}
