<?php

class bors_body_data_paginated extends bors_object
{
	function object() { return $this->id(); }

	function _order_def()
	{
		if($o = bors()->request()->data_parse('signed_name', 'sort'))
			return $o;

		return $this->object()->get('paginated_order', '-create_time'); // Всегда -create_time! Не менять в будущем. Унификация.
	}

	private function _where($where = array())
	{
		$where = array_merge(object_property($this->object(), 'paginated_where', array()), $where);

		if($group = $this->object()->get('paginated_group', false))
			$where['group'] = $group;

		return $where;
	}

	private $_items;
	function items()
	{
		if(!is_null($this->_items))
			return $this->_items;

		try
		{
			$this->_items = bors_find_all($this->object()->paginated_class(), $this->_where(array(
				'page' => $this->object()->page(),
				'per_page' => $this->object()->items_per_page(),
				'order' => $this->order(),
			)));
		}
		catch(Exception $e)
		{
			$this->_items = array();
		}

		if($this->object()->get('is_paging_reversed'))
			$this->_items = array_reverse($this->_items);

		if(method_exists($this->object(), 'on_items_load'))
			$this->object()->on_items_load($this->_items);

		return $this->_items;
	}

	private $_total;
	function total_items()
	{
		if(!is_null($this->_total))
			return $this->_total;

//		try
		{
			$count = $this->_total = bors_count($this->object()->paginated_class(), $this->_where());
		}
//		catch(Exception $e)
		{
//			$count = 0;
		}

		return $count;
	}

	function body_data($data = array())
	{
		$this->object()->set_total_items($this->total_items());
		return array_merge($data, array(
			'items' => $this->items(),
			$this->items_name() => $this->items(),
		));
	}

	function item_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->object()->paginated_class());
	}

	function items_name() { return bors_plural($this->item_name()); }
}
