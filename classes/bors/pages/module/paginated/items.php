<?php

class bors_pages_module_paginated_items extends bors_module
{
	function body_data()
	{
		$items = $this->args('items');

		if($class_name = $this->args('class'))
			$foo = new $class_name(NULL);
		else
			$foo = @$items[0];

		if($foo && !($data = $foo->get('item_list_fields')))
		{
			$data = array(
				'title' => ec('Название'),
				'id' => ec('ID'),
			);
		}

		return array(
			'item_list_fields' => $data,
			'items' => $items,
		) + parent::body_data();
	}
}
