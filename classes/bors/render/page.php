<?php

/*
	Глобальный рендерер страниц. Использует шаблонизатор, описанный
	в методе page_template_engine() выводимого объекта.
*/

class bors_render_page extends base_null
{
	function render($object)
	{
		$template = object_load($template_engine = $object->renderer()->page_template_engine());
		if(!$template)
			return ec("Ошибка: Не могу загрузить механизм шаблонизации страницы '$template_engine'");

		$data = array();
		$data = array_merge($data, $object->global_data());

		$data['body'] = $object->body();
		$data['this'] = $object;

		$template->set_object($object);
		$template->set_template($object->template());
		$template->set_data($data);
//		echo "Render tpl {$object->template()}<br/>";
		return $template->render();
	}
}
