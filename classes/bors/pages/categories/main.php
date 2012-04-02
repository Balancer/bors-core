<?php

// Класс, выводящий список заданных категорий

class bors_pages_categories_main extends bors_page
{
	function body_data()
	{
		return array(
			'categories' => $this->categories(),
		) + parent::body_data();
	}

	function categories()
	{
		$category_class = $this->category_class_name();
		return call_user_func(array($category_class, 'used_categories'), array(
			'*to' => $this->target_classes(),
			'order' => 'title',
		));
	}
}
