<?php

class base_page_paged extends base_page
{
	function where() { return array(); }
	function order() { return '-create_time'; }
	function group() { return false; }

	function class_file()
	{
		$pcf = parent::class_file();
		if(!file_exists(str_replace('.php', '.html', $pcf)))
			return __FILE__;

		return $pcf;
	}

	private function _where($where = array())
	{
		$where = array_merge($this->where(), $where);

		if($group = $this->group())
			$where['group'] = $group;

		return $where;
	}

	private $_items;
	function items()
	{
		if(!is_null($this->_items))
			return $this->_items;
	

		return $this->_items = objects_array($this->main_class(), $this->_where(array(
			'page' => $this->page(),
			'per_page' => $this->items_per_page(),
			'order' => $this->order(),
		)));
	}

	private $_total;
	function total_items()
	{
		if(!is_null($this->_total))
			return $this->_total;

		return $this->_total = objects_count($this->main_class(), $this->_where());
	}

	function template_local_vars() { return parent::template_local_vars().' items'; }
	function url_engine() { return 'url_calling2'; }
}
