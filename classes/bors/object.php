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
//		if($go = $obj->attr('redirect_to'))
//			return go($go);

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

	function direct_content()
	{

		$renderer = $this->renderer();
		if(config('debug.execute_trace'))
			debug_execute_trace("{$this->debug_title_short()} renderer = {$renderer}");

		if(!$renderer)
			bors_throw(ec('Отсутствует рендерер класса ').$this->class_name()." (renderer_class={$this->get('renderer_class')}, render_engine={$this->get('render_engine')})");

		return $renderer->render($this);
	}

	function description_or_title()
	{
		if($desc = $this->description())
			return $desc;

		if($title = $this->title())
			return $title;

		return ec('[без имени]');
	}

	static function called_class_name($self, $class_name = NULL)
	{
		if(function_exists('get_called_class'))
			return get_called_class();

		if($self)
			return $self->class_name();

		if($class_name)
			return $class_name;

		bors_throw(ec('Не указано имя вызывающего класса и его невозможно определить в текущей версии PHP. Укажите имя класса принудительно в $data[\'class_name\']'));
	}

	function admin_additional_info() { return array(); }

	function object_type() { return 'unknown'; }

	function ctime()
	{
		if($this->__havefc())
			return $this->__lastc();

		return bors_load('bors_time', $this->create_time());
	}

	function mtime()
	{
		if($this->__havefc())
			return $this->__lastc();

		return bors_load('bors_time', $this->modify_time());
	}

	function _admin_searchable_properties_def()
	{
		$properties = array();
		foreach(bors_lib_orm::fields($this) as $x)
			if(!empty($x['is_admin_searchable']))
				$properties[] = $x['property'];

		return $properties ? join(' ', $properties) : 'title';
	}

	function _section_name_def() { return bors_core_object_defaults::section_name($this); }

	function call($method_name)
	{
		$args = func_get_args();
		array_shift($args);
//		var_dump($method_name, $args);
		return call_user_func_array(array($this, $method_name), $args);
	}

	function _item_list_admin_fields_def() { return $this->item_list_fields(); }
}
