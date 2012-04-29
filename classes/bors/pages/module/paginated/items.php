<?php

class bors_pages_module_paginated_items extends bors_module
{
	function body_data()
	{
		$class_name = $this->args('class');
		$foo = new $class_name(NULL);
		if(!($data = $foo->get('item_list_fields')))
		{
			$data = array(
				'title' => ec('Название'),
				'id' => ec('ID'),
			);
		}

		return array(
			'item_list_fields' => $data,
			'items' => $this->args('items'),
		) + parent::body_data();
	}
}
