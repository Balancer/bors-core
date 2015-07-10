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
	var $_prop_joins = array();
	var $defaults = array();

	function id() { return $this->___id; }
	function set_id($id) { return $this->___id = $id; }

	function __construct($id)
	{
		$this->set_id($this->initial_id = $id);
	}

	static function foo($id = NULL)
	{
		return bors_foo(get_called_class(), $id);
	}

	function get($name, $default = NULL, $skip_methods = false, $skip_properties = false)
	{
		static $get_lock = array();
		$lock_name = get_class($this).'/'.serialize($this->id()).'.'.$name;

		if(!$name || !empty($get_lock[$lock_name]))
//		if(!$name)
			return NULL;

		$get_lock[$lock_name] = true;

		if(!preg_match('/^\w+$/', $name))
		{
			// Если оформлено как функциональный вызов

			// Хак: если это SQL-id вида 'id' => "CONCAT_WS('-', person_id, list_id)",
			if(preg_match('/^CONCAT/', $name))
			{
				unset($get_lock[$lock_name]);
				return NULL;
			}

			$result = NULL;

			if(preg_match('/^(.+)\|(\w+)$/', $name, $m))
			{
				$name = $m[1];
				$post_function = $m[2];
			}
			else
				$post_function = NULL;

			try
			{
				$result = $this;
				$ok = true;

				// Если сразу вызывать eval для цепочек $this->target()->titled_link(),
				// то вываливается неперехватываемое исключение, если target() вернёт NULL
				// Поэтому пробуем разобрать по цепочке.
				foreach(explode('->', $name) as $method)
				{
					if(preg_match('/(\w+)\(\)/', $method, $m))
					{
						// Если следом вызов метода от NULL, то облом и возвращаем умолчение.
						if(is_null($result))
						{
							unset($get_lock[$lock_name]);
							return $default;
						}

						$result = call_user_func(array($result, $m[1]));
					}
					else
					{
						// Попался не чисто функциональный вызов. Если так, то пробуем, всё же, eval();
						$ok = false;
						break;
					}
				}

				if(!$ok)
					eval("\$result = \$this->$name;");

                /** @var string|null $post_function */
				if($post_function)
                    $result = $post_function($result);
			}
			catch(Exception $e)
			{
				$result = $default;
			}

			unset($get_lock[$lock_name]);
			return $result;
		}

		if(method_exists($this, $name) && !$skip_methods)
		{
			$value = NULL;
			try { $value = $this->$name(); }
			catch(Exception $e) { $value = NULL; }
			unset($get_lock[$lock_name]);
			return $value;
		}

		// У атрибутов приоритет выше, так как они могут перекрывать data.
		// Смотри также в __call
		if(array_key_exists($name, $this->attr))
		{
			unset($get_lock[$lock_name]);
			return $this->attr[$name];
		}

		if(!empty($this->data) && array_key_exists($name, $this->data))
		{
			unset($get_lock[$lock_name]);
			return $this->data[$name];
		}

		if($name == 'this')
		{
			unset($get_lock[$lock_name]);
			return $this;
		}

		// Проверяем параметры присоединённых объектов
		if($prop_joins = @$this->_prop_joins)
			foreach($prop_joins as $x)
				if(array_key_exists($name, $x->data))
				{
					unset($get_lock[$lock_name]);
					return $this->attr[$name] = $x->data[$name];
				}

		// Проверяем автоматические объекты.
		if(method_exists($this, 'auto_objects'))
		{
			$auto_objs = $this->auto_objects();
			if(!empty($auto_objs[$name]))
				if(preg_match('/^(\w+)\((\w+)\)$/', $auto_objs[$name], $m))
				{
					try { $value = bors_load($m[1], $this->get($m[2])); }
					catch(Exception $e) { $value = NULL; }
					unset($get_lock[$lock_name]);
					return $this->attr[$name] = $value;
				}
		}

		// Автоматические целевые объекты (имя класса задаётся)
		if(method_exists($this, 'auto_targets'))
		{
			$auto_targs = $this->auto_targets();
			if(!empty($auto_targs[$name]))
				if(preg_match('/^(\w+)\((\w+)\)$/', $auto_targs[$name], $m))
				{
					unset($get_lock[$lock_name]);
					return $this->attr[$name] = bors_load($this->get($m[1]), $this->get($m[2]));
				}
		}

		// Проверяем одноимённые переменные (var $title = 'Files')
		if(property_exists($this, $name) && !$skip_properties)
		{
			unset($get_lock[$lock_name]);
			return $this->set_attr($name, $this->$name);
		}

		// Проверяем одноимённые переменные, требующие перекодирования (var $title_ec = 'Сообщения')
		$name_ec = "{$name}_ec";
		if(property_exists($this, $name_ec) && !$skip_properties)
		{
			unset($get_lock[$lock_name]);
			return $this->set_attr($name, ec($this->$name_ec));
		}

		// Ищем методы, перекрываемые переменным по умолчанию
		$m = "_{$name}_def";
		if(method_exists($this, $m) && !$skip_methods)
		{
			try { $value = $this->$m(); }
			catch(Exception $e) { $value = NULL; }
			unset($get_lock[$lock_name]);
			return $this->attr[$name] = $value;
		}

		// С этого места и ниже никаких потенциально рекурсивных вызовов быть не должно, так что разлочиваем.
		unset($get_lock[$lock_name]);

		if(array_key_exists($name, $this->defaults))
			return $this->defaults[$name];

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
			if(!empty($auto_objs[$name]))
				if(preg_match('/^(\w+)\((\w+)\)$/', $auto_objs[$name], $m))
					return $this->attr[$name] = object_load($m[1], $this->$m[2]());
		}

		// Автоматические целевые объекты (имя класса задаётся)
		if(method_exists($this, 'auto_targets'))
		{
			$auto_targs = $this->auto_targets();
			if(!empty($auto_targs[$name]))
				if(preg_match('/^(\w+)\((\w+)\)$/', $auto_targs[$name], $m))
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

	function _body_engine_def() { return ''; }

	function _storage_engine_def() { return ''; }
	function is_loaded() { return true; }
	function internal_uri() { return get_class($this).'://'.$this->id(); }
	function cache_clean() { }

	function auto_search_index() { return false; }
	function __toString() { return $this->class_name().'://'.$this->id(); }

	function attr($attr, $def = NULL) { return array_key_exists($attr, $this->attr) ? $this->attr[$attr] : $def; }
	function set_attr($attr, $value) { return $this->attr[$attr] = $value; }

	function set_attrs($attrs)
	{
		$this->attr = array_merge($this->attr, $attrs);
		return $this;
	}

	function set_property($property, $value, $db_up = true)
	{
		$this->set($property, $value, $db_up);
		return $this;
	}

	function set_properties($properties, $db_up = true)
	{
		foreach($properties as $property => $value)
			$this->set($property, $value, $db_up);

//		var_dump($db_up, $properties, $this->body_data());

		return $this;
	}

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
