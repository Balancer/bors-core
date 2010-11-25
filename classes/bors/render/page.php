<?php

/*
	Глобальный рендерер страниц. Использует шаблонизатор, описанный
	в методе page_template_engine() выводимого объекта.

	Класс рендеринга обычных HTML-страниц.
	Каждая страница состоит из двух условных полей - «внешней» части страницы (общий дизайн)
	и «внутренней» - тела страницы. Эти части носят стандартный корень 'page' и 'body', соответственно.

	Каждая часть может использовать отдельный шаблонизатор.

	По умолчанию используется Smarty 2.

	Фактически этот вид рендера получает тело страницы через метод body() (который уже и вызывает
	рендеринг тела) и подставляет его с нужными прочими данным в шаблон страницы.
*/

class bors_render_page extends base_null
{
	function render($object)
	{
		$template = bors_load_smart($template_engine = $object->renderer()->page_template_engine());
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
