<?php

// Класс, выводящий список объектов, привязанных к заданной категории

class bors_pages_categories_list extends bors_page
{
	function _title_def() { return $this->category()->title(); }
	function _nav_name_def() { return $this->category()->nav_name(); }

	function body_data()
	{
		return array(
			'items' => $this->items(),
		) + parent::body_data();
	}

	function category() { return bors_load($this->category_class_name(), $this->id()); }

	// Отладка на:
	//	• http://aviaport.wrk.ru/photos/regions/50/
	function items()
	{
		return $this->category()->linked_objects(array(
			'*to' => $this->target_classes(),
			'order' => '-create_time',
		));
	}
}
