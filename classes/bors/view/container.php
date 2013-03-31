<?php

/**
	Типовой класс для view объекта с отображением субобъектов с разбивкой на страницы
*/

class bors_view_container extends bors_view
{
	// Класс отображаемого объекта
	function container_class() { return bors_throw(ec('Вы не переопределили класс отображаемого объекта')); }

	// Класс субобъектов
	function nested_class() { return bors_throw(ec('Вы не переопределили класс вложенного объекта')); }

	// Имя поля вложенного объекта, в котором хранится ID контейнера
	function container_id_property() { return $this->container_name().'_id'; }

	function where() { return array(
		$this->container_id_property() => $this->id(),
		'by_id' => true,
	); }
	function order() { return '-create_time'; }
	function group() { return false; }

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'container' => 'main_class(id)',
			$this->container_name() => 'main_class(id)',
		));
	}

	function on_items_load(&$items) { }

	function nested_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->nested_class());
	}

	function container_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->container_class());
	}

	function total_pages() { return intval($this->topic()->total_pages()); }

	private function _where($where = array())
	{
		$where = array_merge($where, $this->where());

		if($group = $this->group())
			$where['group'] = $group;

		return $where;
	}

	function on_nested_load(&$items) { }

	private $_items;
	function items()
	{
		if(!is_null($this->_items))
			return $this->_items;

		try {
			$this->_items = bors_find_all($this->nested_class(), $this->_where(array(
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

		$this->on_nested_load($this->_items);
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
			$count = $this->_total = bors_count($this->nested_class(), $this->_where());
		}
		catch(Exception $e)
		{
			$count = 0;
		}

		return $count;
	}

//	function template_local_vars() { return parent::template_local_vars().' '.bors_plural($this->nested_name()); }
	function body_data()
	{
		$items = $this->items();

		return array_merge(parent::body_data(), array(
			bors_plural($this->nested_name()) => $items
		));
	}

	function url_engine() { return 'url_calling2'; }

	function default_page() { return $this->is_reversed() ? $this->total_pages() : 1; }
	function visits_inc($inc = 1) { return $this->container()->visits_inc($inc); }
}
