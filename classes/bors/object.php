<?php

bors_funcs::init();

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
		if(empty($GLOBALS['bors-orm-cache']['auto_objects_append']))
			$map = array();
		else
			$map = $GLOBALS['bors-orm-cache']['auto_objects_append'];

		$map['mtime'] = 'bors_time(modify_time)';

		return $map;
/*
		return array(
			'user'  => 'bors_user(user_id)',
			'owner' => 'bors_user(owner_id)',
			'last_editor' => 'bors_user(last_editor_id)',
		);
*/
	}

	// Предустановленные авто целевые объекты
	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'target' => 'target_class_name(target_id)',
		));
	}

	function is_value() { return true; }

/*
	function show()
	{
//		if($go = $obj->attr('redirect_to'))
//			return go($go);

		return false; // Пока ничего автоматом не выводим
		//TODO: добавить debug-info в конец и т.п. вещи из main.php
//		echo $this->content();
//		return true;
	}
*/

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
		$renderer_class = $this->get('template_class');

		if(!$renderer_class)
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
		{
			$view_class = $this->get('view_class');
			if($view_class && ($view = bors_load($view_class, $this)))
			{
				$view->set_model($this);
				return $view->content();
			}

			bors_throw(ec('Отсутствует рендерер класса ').$this->class_name()." (renderer_class={$this->get('renderer_class')}, render_engine={$this->get('render_engine')})");
		}

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

	function _admin_searchable_title_properties_def()
	{
		return $this->___admin_searchable_properties_def(false);
	}

	function _admin_searchable_properties_def()
	{
		return $this->___admin_searchable_properties_def(true);
	}

	function ___admin_searchable_properties_def($any)
	{
		$properties = array();
		$title_properties = array();
		foreach(bors_lib_orm::fields($this) as $x)
			if(!empty($x['is_admin_searchable']))
				if(empty($x['is_title']))
					$properties[] = $x['property'];
				else
					$title_properties[] = $x['property'];

		if($title_properties && !$any)
			$properties = $title_properties;

		if(!$properties)
		{
			$properties[] = 'id';
			$properties[] = 'title';
		}

		if($x = $this->get('searchable_title_properties'))
			$properties += $x;

		if($any && ($x = $this->get('searchable_more_properties')))
			$properties += $x;

		return join(' ', array_unique($properties));
	}

	function _section_name_def() { return bors_core_object_defaults::section_name($this); }
	function _is_changes_logging_def() { return false; }

	function call($method_name)
	{
		$args = func_get_args();
		array_shift($args);
//		var_dump($method_name, $args);
		if(method_exists($this, $method_name))
			return call_user_func_array(array($this, $method_name), $args);

		return NULL;
	}

	function _item_list_admin_fields_def() { return $this->get('item_list_fields'); }

	function module($module_name) { return $this->module_ex($module_name, array()); }

	// http://aviaport.wrk.ru/directory/aviafirms/groups/
	function module_ex($module_name, $attrs)
	{
		$class_name = $this->class_name();
		$class_name = preg_replace('/_(main|edit|view)$/', '', $class_name);
		$module_class = bors_plural($class_name).'_modules_'.$module_name;
		set_def($attrs, 'model', $this);
		$mod = bors_load_ex($module_class, $this, $attrs);
		if(!$mod)
			bors_throw(ec("Не могу загрузить модуль '$module_class'"));

		return $mod;
	}

	function uses($asset, $args = NULL)
	{
//		if($asset == 'composer')
//			return require_once(__DIR__.'/../../../../autoload.php');

		bors_throw("Unknown uses $asset");
//		return parent::uses($asset, $args);
	}

	// Добавить свойства другого объекта к свойствам нашего
	function _set_prop_join($join_object)
	{
		$this->_prop_joins[] = $join_object;
	}

	function __class_cache_base()
	{
		return config('cache_dir').'/classes/'.str_replace('_', '/', get_class($this));
	}

	static $__cache_data = array();
	function class_cache_data($name = NULL, $setter = NULL)
	{
		if(empty(self::$__cache_data[$this->class_name()]))
		{
			if(file_exists($f = $this->__class_cache_base().'.data.json'))
				$data = json_decode(file_get_contents($f), true);

			if(empty($data['class_mtime']) || $data['class_mtime'] != filemtime($this->class_file()))
				self::$__cache_data[$this->class_name()] = $data = array();
			else
				self::$__cache_data[$this->class_name()] = $data;
		}

		if(!$name)
			return empty(self::$__cache_data[$this->class_name()]) ? array() : self::$__cache_data[$this->class_name()];

		if(!empty(self::$__cache_data[$this->class_name()]) && array_key_exists($name, self::$__cache_data[$this->class_name()]))
			return self::$__cache_data[$this->class_name()][$name];

		if($setter)
			return $this->set_class_cache_data($name, call_user_func($setter));

		return NULL;
	}

	function set_class_cache_data($name, $value)
	{
		return bors_class_loader::set_class_cache_data($this->class_name(), $this->class_file(), $name, $value);
	}

	function property_info($property_name)
	{
		foreach(bors_lib_orm::all_fields($this) as $f)
			if($f['property'] == $property_name)
				return $f;

		return bors_throw("Can't find property $property_name in ".$this->class_name());
	}
}
