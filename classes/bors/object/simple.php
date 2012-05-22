<?php

/**
	Минимальный полноценный BORS-объект
	Всё необходимое для полноценной работы
	Без бэкенда.
*/

class bors_object_simple extends bors_object_empty
{
	var $___id;

	var $attr = array();
	var $defaults = array();

	function id() { return $this->___id; }
	function set_id($id) { return $this->___id = $id; }

	function __construct($id)
	{
		$this->set_id($this->initial_id = $id);
	}

	function get($name, $default = NULL, $skip_methods = false, $skip_properties = false)
	{
		if(!preg_match('/^\w+$/', $name))
		{
			// Если оформлено как функциональный вызов
			// Хак: если это SQL-id вида 'id' => "CONCAT_WS('-', person_id, list_id)",

			if(preg_match('/^CONCAT/', $name))
				return NULL;

			$result = NULL;

			try { eval("\$result = \$this->$name;"); }
			catch(Exception $e) { $result = NULL; }

			return $result;
		}

		if(method_exists($this, $name) && !$skip_methods)
		{
			$value = NULL;
			try { $value = $this->$name(); }
			catch(Exception $e) { $value = NULL; }
			return $value;
		}

		// У атрибутов приоритет выше, так как они могут перекрывать data.
		// Смотри также в __call
		if(array_key_exists($name, $this->attr))
			return $this->attr[$name];

		if(@array_key_exists($name, $this->data))
			return $this->data[$name];

		if($name == 'this')
			return $this;

		// Проверяем автоматические объекты.
		if(method_exists($this, 'auto_objects'))
		{
			$auto_objs = $this->auto_objects();
			if(($f = @$auto_objs[$name]))
				if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
					return $this->attr[$name] = bors_load($m[1], $this->$m[2]());
		}

		// Автоматические целевые объекты (имя класса задаётся)
		if(method_exists($this, 'auto_targets'))
		{
			$auto_targs = $this->auto_targets();
			if(($f = @$auto_targs[$name]))
				if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
					return $this->attr[$name] = bors_load($this->get($m[1]), $this->get($m[2]));
		}

		// Проверяем одноимённые переменные (var $title = 'Files')
		if(property_exists($this, $name) && !$skip_properties)
			return $this->set_attr($name, $this->$name);

		// Проверяем одноимённые переменные, требующие перекодирования (var $title_ec = 'Сообщения')
		$name_ec = "{$name}_ec";
		if(property_exists($this, $name_ec) && !$skip_properties)
			return $this->set_attr($name, ec($this->$name_ec));

		// Почему-то раньше нотации шли после _{name}_def. Не логично, так как нотации должны перекрывать значения по умолчанию
		// Но если где-то вылезут ошибки, нужно будет думать.
		$x = bors_lib_orm::get_notation($this, $name);
		if($x !== false)
			return $this->attr[$name] = $x;

		// Ищем методы, перекрываемые переменным по умолчанию
		$m = "_{$name}_def";
		if(method_exists($this, $m) && !$skip_methods)
		{
			try { $value = $this->$m(); }
			catch(Exception $e) { $value = NULL; }
			return $this->attr[$name] = $value;
		}

		if(@array_key_exists($name, $this->defaults))
			return $this->defaults[$name];

		if(bors_lib_orm::get_yaml_notation($this, $name))
			return $this->attr[$name];

		return $default;
	}

	function get_ne($name, $def = NULL)
	{
		if(is_array($name))
		{
			$def = popval($name, 'default');
			$name = popval($name, 'property');
		}

		$val = $this->get($name);
		if($val)
			return $val;

		if(is_array($def) && !empty($def['property']))
			return $this->get_ne($def);

		return $def;
	}

	function get_data($name, $default = NULL, $auto_set = false)
	{
		if(@array_key_exists($name, $this->data))
			return $this->data[$name];

		if(array_key_exists($name, $this->attr))
			return $this->attr[$name];

		// Проверяем автоматические объекты.
		if(method_exists($this, 'auto_objects'))
		{
			$auto_objs = $this->auto_objects();
			if(($f = @$auto_objs[$name]))
				if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
					return $this->attr[$name] = object_load($m[1], $this->$m[2]());
		}

		// Автоматические целевые объекты (имя класса задаётся)
		if(method_exists($this, 'auto_targets'))
		{
			$auto_targs = $this->auto_targets();
			if(($f = @$auto_targs[$name]))
				if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
					return $this->attr[$name] = object_load($this->$m[1](), $this->$m[2]());
		}

		// Проверяем одноимённые переменные (var $title = 'Files')
		if(property_exists($this, $name))
			return $this->set_attr($name, $this->$name);

		// Проверяем одноимённые переменные, требующие перекодирования (var $title_ec = 'Сообщения')
		$name_ec = "{$name}_ec";
		if(property_exists($this, $name_ec))
			return $this->set_attr($name, ec($this->$name_ec));

		return $auto_set ? $this->set_attr($name, $default) : $default;
	}

	function is_set($name, $skip_methods = false, $skip_properties = false)
	{
		if(method_exists($this, $name) && !$skip_methods)
			return true;

		if(array_key_exists($name, $this->data))
			return true;

		if(array_key_exists($name, $this->attr))
			return true;

		// Проверяем автоматические объекты.
		if(method_exists($this, 'auto_objects'))
		{
			$auto_objs = $this->auto_objects();
			if(($f = @$auto_objs[$name]))
				if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
					return true;
		}

		// Автоматические целевые объекты (имя класса задаётся)
		if(method_exists($this, 'auto_targets'))
		{
			$auto_targs = $this->auto_targets();
			if(($f = @$auto_targs[$name]))
				if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
					return true;
		}

		// Проверяем одноимённые переменные (var $title = 'Files')
		if(property_exists($this, $name) && !$skip_properties)
			return true;

		// Проверяем одноимённые переменные, требующие перекодирования (var $title_ec = 'Сообщения')
		$name_ec = "{$name}_ec";
		if(property_exists($this, $name_ec) && !$skip_properties)
			return true;

		return false;
	}

	function attr_preset()
	{
		return array_merge(parent::attr_preset(), array(
			'body_engine'	=> '',
		));
	}

	function storage_engine() { return ''; }
	function loaded() { return true; }
	function internal_uri() { return get_class($this).'://'.$this->id(); }
	function cache_clean() { }

	function auto_search_index() { return false; }
	function __toString() { return $this->class_name().'://'.$this->id(); }

	function attr($attr, $def = NULL) { return array_key_exists($attr, $this->attr) ? $this->attr[$attr] : $def; }
	function set_attr($attr, $value) { return $this->attr[$attr] = $value; }
	function append_attrs($attrs) { return $this->attr = array_merge($this->attr, $attrs); }

//	private $__last_cache_key; // идентификатор последнего проверяемого по havec значения
	var $__last_cache_stack = array(); // Для реентерабельности
	function __havec($attr)
	{
		array_push($this->__last_cache_stack, $attr);
		return array_key_exists($attr, $this->attr);
	}
	function __lastc()
	{
		return $this->attr[array_pop($this->__last_cache_stack)];
	}

	function __setc($value)
	{
		return $this->attr[array_pop($this->__last_cache_stack)] = $value;
	}

	function __havefc()
	{
		$attr = '__cache_'.calling_function_name();
		array_push($this->__last_cache_stack, $attr);
		return array_key_exists($attr, $this->attr);
	}

	function load_attr($attr, $init)
	{
		if(array_key_exists($attr, $this->attr))
			return $this->attr[$attr];

		debug_hidden_log('__need-to-rewrite-ugly-code', 'load-attr: '.$attr);
		return $this->attr[$attr] = $init;
	}

	public function __sleep() { return array_keys(get_object_vars($this)); }

/*	Лежит с целью отладки
	function __set($name, $value)
	{
		if($name == 'title' && $value == 'p1990_dealers_admin_files')
		{
			echo "Set $name to $value<br/>";
			echo debug_trace();
		}
		return $this->$name = $value;
	}
*/
}
