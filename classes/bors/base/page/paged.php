<?php

class base_page_paged extends base_page
{
	function where() { return array(); }
	function order() { return '-modify_time'; }

	function items()
	{
		$where = $this->where();
		$where['page'] = $this->page();
		$where['per_page'] = $this->items_per_page();
		if($order = $this->order())
			$where['order'] = $order;
		
		return objects_array($this->main_class(), $where);
	}

	function total_items() { return objects_count($this->main_class(), $this->where()); }

	function template_local_vars() { return parent::template_local_vars().' items'; }
}
