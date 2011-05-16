<?php

class bors_rss extends base_rss
{
	function title() { return bors_throw(ec('Не указан заголовок RSS-ленты')); }
	function items_class_name() { return bors_throw(ec('Не указан класс объектов RSS-ленты')); }

	function limit() { return 20; }
	function order() { return '-create_time'; }
	function where() { return array(); }

	function rss_items()
	{
		return bors_find_all($this->items_class_name(), array_merge(array(
			'order' => $this->order(),
			'limit' => $this->limit(),
		), $this->where()));
	}

//	function main_url() { return $this->called_url(); }
	function rss_url()  { return $this->called_url(); }


}
