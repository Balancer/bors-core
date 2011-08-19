<?php

class base_page_paged extends base_page
{
	function where() { return array(); }
	function order() { return '-create_time'; }
	function group() { return false; }

	private function _where($where = array())
	{
		$where = array_merge(object_property($this, 'where', array()), $where);

		if($group = $this->group())
			$where['group'] = $group;

		return $where;
	}

	function on_items_load(&$items) { }

	private $_items;
	function items()
	{
		if(!is_null($this->_items))
			return $this->_items;

		try {
		$this->_items = objects_array($this->main_class(), $this->_where(array(
			'page' => $this->page(),
			'per_page' => $this->items_per_page(),
			'order' => $this->order(),
		)));
		}
		catch(Exception $e)
		{
			$this->_items = array();
		}

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

		try
		{
			$count = $this->_total = objects_count($this->main_class(), $this->_where());
		}
		catch(Exception $e)
		{
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

	function auto_objects()
	{
		return array_merge(parent::auto_objects(), array(
			bors_plural($this->item_name()) => $this->items(),
		));
	}
}
