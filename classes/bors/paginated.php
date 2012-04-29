<?php

class bors_paginated extends base_page_paged
{
	function item_fields()
	{
		$class_name = $this->main_class();
		$foo = new $class_name(NULL);
		if($data = $foo->get('item_list_fields'))
			return $data;

		return array(
			'title' => ec('Название'),
			'id' => ec('ID'),
		);
	}
}
