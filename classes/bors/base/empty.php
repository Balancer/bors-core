<?php

class base_empty extends base_null
{
	var $___id;

	var $attr = array();
	var $defaults = array();

	function id() { return $this->___id; }
	function set_id($id) { return $this->___id = $id; }

	function __construct($id)
	{
//		echo get_class($this)."<br/>";
		$this->set_id($this->initial_id = $id);
	}

	function get($name, $default = NULL, $skip_methods = false, $skip_properties = false)
	{
		if(method_exists($this, $name) && !$skip_methods)
		{
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

		if(@array_key_exists($name, $this->defaults))
			return $this->defaults[$name];

		return $default;
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
