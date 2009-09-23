<?php

class base_page_paged extends base_page
{
	function where() { return array(); }
	function order() { return '-create_time'; }
	function group() { return false; }

	function body_template()
	{
		$current_class = get_class($this);
		$class_files = $GLOBALS['bors_data']['class_included'];
		$ext = $this->body_template_ext();

		while($current_class)
		{
			$template_file = preg_replace("!\.php$!", "$1.$ext", $class_files[$current_class]);
			if(file_exists($template_file))
				break;
			$current_class = get_parent_class($current_class);
		}

		return "xfile:{$template_file}";
	}

	private function _where($where = array())
	{
		$where = array_merge($this->where(), $where);

		if($group = $this->group())
			$where['group'] = $group;

		return $where;
	}

	function on_items_load($items) { }

	private $_items;
	function items()
	{
		if(!is_null($this->_items))
			return $this->_items;

		$this->_items = objects_array($this->main_class(), $this->_where(array(
			'page' => $this->page(),
			'per_page' => $this->items_per_page(),
			'order' => $this->order(),
		)));

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

		return $this->_total = objects_count($this->main_class(), $this->_where());
	}

	function template_local_vars() { return parent::template_local_vars().' items'; }
	function url_engine() { return 'url_calling2'; }

	function default_page() { return $this->is_reversed() ? $this->total_pages() : 1; }
}
