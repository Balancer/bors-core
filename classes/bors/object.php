<?php

// Идёт процесс рефакторинга с переносом функционала base_object в bors_object
class bors_object extends base_object
{
	// Общая структура имён
	// show() - показывает объект, с кешированием и прочим.
	// action() - обрабатывает результаты работы форм.
	// content() - возвращает полное содержимое объекта для вывода в браузер. Некешированное.
	//			Для страниц использует content_render(), использующий content_template().
	// body() - возвращает в случае page-классов содержимое внутренней части страницы
	//			Для страниц использует body_render(), использующий body_template().
	// html() - возвращает код для вставки объекта в виде HTML.
	// storage() - возвращает бэкенд класса
	// url() - ссылка на объект
	// link() - HTML-текст ссылки на объект

	// Генерируемые классы:
	//	bors_user - класс пользователя по умолчанию.

	// Предустановленные автообъекты
	function auto_objects()
	{
		return array(
			'user'  => 'bors_user(user_id)',
			'owner' => 'bors_user(owner_id)',
			'last_editor' => 'bors_user(last_editor_id)',
		);
	}

	// Предустановленные авто целевые объекты
	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'target' => 'target_class_name(target_id)',
		));
	}

	function show()
	{
		return false; // Пока ничего автоматом не выводим
		//TODO: добавить debug-info в конец и т.п. вещи из main.php
//		echo $this->content();
//		return true;
	}

	// возвращает полное содержимое объекта для вывода в браузер. Некешированное.
	function __content() // пока не используется, т.к. более древнее в base_object
	{
		if(($render_class = $this->render_class()))
		{
			if($render_class == 'self')
				$render = $this;
			elseif(!($render = bors_load($render_class)))
				debug_exit("Can't load global render engine {$render_class} for object '{$object}'");

			return $render->rendering($this);
		}
	}

	function renderer()
	{
		$renderer_class = $this->get('renderer_class');

		if(!$renderer_class)
			$renderer_class = $this->get('render_engine'); // Старый API, для совместимости.

		if($renderer_class == 'self')
			return $this;

		return $renderer_class ? bors_load($renderer_class, NULL) : NULL;
	}

	function direct_content() { return $this->renderer()->render($this); }

	function description_or_title()
	{
		if($desc = $this->description())
			return $desc;

		if($title = $this->title())
			return $title;

		return ec('[без имени]');
	}
}
