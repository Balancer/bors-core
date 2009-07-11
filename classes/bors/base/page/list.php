<?php

class base_page_list extends base_page
{
	function where() { return array(); }
	function order() { return '-create_time'; }
	function group() { return false; }
	function limit() { return false; }

	private function _where($where = array())
	{
		$where = array_merge($this->where(), $where);

		if($group = $this->group())
			$where['group'] = $group;

		if($limit = $this->limit())
			$where['limit'] = $limit;

		return $where;
	}

	function items_prepare(&$items) { }

	private $_items;
	function items()
	{
		if(!is_null($this->_items))
			return $this->_items;

		$this->_items = objects_array($this->main_class(), $this->_where(array(
			'order' => $this->order(),
		)));

		$this->items_prepare($this->_items);

		return $this->_items;
	}

	function template_local_vars() { return parent::template_local_vars().' items'; }
	function url_engine() { return 'url_calling2'; }
}
