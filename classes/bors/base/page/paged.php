<?php

class base_page_paged extends bors_page
{
	function _where_def() { return array(); }
	function _order_def() { return '-create_time'; } // Всегда! Не менять в будущем. Унификация.
	function _group_def() { return false; }

	function _inner_join_def() { return false; }
	function _item_properties_set_def() { return false; }
	function _limit_def() { return false; }

	private function _where($where = array())
	{
		$where = array_merge(object_property($this, 'where', array()), $where);

		if($set = $this->item_properties_set())
			$where['*set'] = $set;

		if($group = $this->group())
		{
			$where['group'] = $group;
			if($this->get('__no_join'))
			{
				if($this->get('counts_in_list'))
				{
					$count = 'COUNT(*) AS `group_count`';
					if(empty($where['*set']))
						$where['*set'] = $count;
					else
						$where['*set'] .= ",$count";
				}
			}
		}

		if($inner = $this->inner_join())
			$where['inner_join'] = $inner;

		if($limit = $this->limit())
			$where['limit'] = $limit;

		return $where;
	}

	function on_items_load(&$items)
	{
		if($preload = $this->get('postload_objects'))
		{
			$parsed = array();

			foreach($preload as $property => $class_name)
			{
				if(preg_match('/^(\w+)\((\w+)\)$/', $class_name, $m))
				{
					$class_name = $m[1];
					$id_property = $m[2];
				}
				else
					$id_property = $property . '_id';

				$parsed[$id_property] = array($class_name, $property);
			}

			$objs = array();

			foreach(bors_fields_array_extract($items, array_keys($parsed)) as $id_property => $values)
			{
				list($class_name, $property) = $parsed[$id_property];
				bors_find_all($class_name, array('id IN' => $values));
			}
		}
	}

	private $_items;
	function items()
	{
		if(!is_null($this->_items))
			return $this->_items;

//		try
		{
			$this->_items = bors_find_all($this->main_class(), $this->where(array(
				'page' => $this->page(),
				'per_page' => $this->items_per_page(),
				'order' => $this->order(),
			)));
		}
/*
		catch(Exception $e)
		{
			$msg = bors_lib_exception::catch_trace($e);
			debug_hidden_log('items_list_exception', $msg);
			$this->_items = array();
		}
*/
		if($this->is_reversed())
			$this->_items = array_reverse($this->_items);

		$this->on_items_load($this->_items);

		return $this->_items;
	}

	private $_total;
	function total_items()
	{
		if(!is_null($this->_total))
			return $this->_total;

		// Если уберём try, проверить на http://admin.aviaport.ru/commerce/pressrelease/export.xls
		try
		{
			$count = $this->_total = bors_count($this->main_class(), $this->where());
		}
		catch(Exception $e)
		{
			$msg = bors_lib_exception::catch_trace($e);
			debug_hidden_log('paginated_items_count_exception', $msg);

			var_dump(get_class($this), $this->where(), $this->get('inner_join_filter'));
			print_dd($msg);

			$count = 0;
		}

		return $count;
	}

	function template_local_vars() { return parent::template_local_vars().' items'; }
	function url_engine() { return 'url_calling2'; }

	function default_page() { return $this->is_reversed() ? $this->total_pages() : 1; }

	function item_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->main_class());
	}

	function items_name() { return bors_plural($this->item_name()); }

	function body_data()
	{
//		print_dd($this->config()->object_data());

		$items = $this->items();

		return array_merge(parent::body_data(), array(
			$this->items_name() => $items,
			'items' => $items,
		));
	}

/*
	// Использовать тут auto_objects — это был былинный отказ
	// __call тоже нельзя использовать, ибо item_name может вызывать определённый
	// как переменная main_class
	//TODO: надо думать.
	function __xcall($method, $params)
	{
		if($method == bors_plural($this->item_name()))
			return $this->items();

		return parent::__call($method, $params);
	}
*/
}
