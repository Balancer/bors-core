<?php

/*
	Глобальный рендерер страниц. Использует шаблонизатор, описанный
	в методе page_template_class() выводимого объекта.

	Класс рендеринга обычных HTML-страниц.
	Каждая страница состоит из двух условных полей - «внешней» части страницы (общий дизайн)
	и «внутренней» - тела страницы. Эти части носят стандартный корень 'page' и 'body', соответственно.

	Каждая часть может использовать отдельный шаблонизатор.

	По умолчанию используется Smarty3.

	Фактически этот вид рендера получает тело страницы через метод body() (который уже и вызывает
	рендеринг тела) и подставляет его с нужными прочими данным в шаблон страницы.

	Структура фреймворка также гарантирует, что шаблонизация тела страницы выполнится раньше шаблонизации
	всей страницы. Таким образом, в шаблоне тела страницы можно устанавливать глобальные переменные
	для последующего использования в шаблоне всей страницы.
*/

class bors_renderers_page extends base_null
{
	static function render($object)
	{
		$data = array();
		$data = array_merge($data, $object->global_data());

		$data['body'] = $object->body();
		$data['self'] = $object;
		$data['this'] = $object;

		$data['main_uri'] = @$GLOBALS['main_uri'];
		$data['now'] = time();
		$data['ref'] = @$_SERVER['HTTP_REFERER'];

		//TODO: убрать user_id и user_name в старых шаблонах.
		$me = bors()->user();
		$data['me'] = $me;
		if($me)
		{
			$data['my_id'] = $me->id();
			$data['my_name'] = $me->title();
		}

		foreach(explode(' ', $object->template_vars()) as $var)
			$data[$var] = $object->$var();

		foreach(explode(' ', $object->template_local_vars()) as $var)
			$data[$var] = $object->$var();

		$data = bors_template::page_data(array_merge($object->data, $data, $object->local_template_data_array()));

//	$smarty->assign("views_average", sprintf("%.1f",86400*$views/($views_last-$views_first+1)));

		$page_template = call_user_func(
			array($object->page_template_class(), 'find_template'),
				$object->page_template(), $object
		);

		return call_user_func(
			array($object->page_template_class(), 'fetch'),
			$page_template,
			$data
		);
	}
}
