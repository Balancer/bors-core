<?php

bors_function_include('cache/global_key');
bors_function_include('cache/set_global_key');

class bors_lib_orm
{
	static $default_field_names = array(
		'title' => 'Название',
		'description' => 'Описание',
		'comment' => 'Комментарий',
		'begin_date' => 'Дата начала',
		'end_date' => 'Дата окончания',
	);

	static function field($property, &$field = NULL)
	{
		// Если это запись вида array('id', 'title', ...);
		if(is_numeric($property) && !is_null($field))
			$property = $field;

		if(!$field)
			$field = $property;

		// Если описание поля не массив, а строка
		if(!is_array($field))
		{
			if(strpos($field, '|') !== false && preg_match('/^(\w+)\|(\w+)$/', $field, $m))
			// Это запись вида 'property' => 'field|post_function'
				$field = array('name' => $m[1], 'post_function' => $m[2]);
			elseif(preg_match('/^(\w+),(\w+)$/', $field, $m))
			//	Запись вида 'id' => 'company_id,user_id' — составной первичный или уникальный ключ
				$field = array('name' => "CONCAT(`{$m[1]}`,':::',`{$m[2]}`)");
			else // просто строка вида 'property' => 'field',
				$field = array('name' => $field);

			$field['sql_name'] = $field['name'];
		}
		else // Описание — массив параметров
		{
			if(empty($field['name']))
				$field['name'] = defval($field, 'field', $property);

			$field['sql_name'] = $field['name'];
		}

		if(preg_match('!^(\w+)\(`(\w+)`\)$!', $field['name'], $m))
		{
			$field['name'] = $m[2];
			$field['sql_function'] = $m[1];
		}

		// Если имя поля вида 'Header|bors_entity_decode', то вторая часть — постфункция.
		if(preg_match('!^(\w+)\|(\w+)$!', $field['name'], $m))
		{
			$field['sql_name'] = $field['name'] = $m[1];
			$field['post_function'] = $m[2];
		}

		$field['property'] = $property;

		if(empty($field['type']))
		{
			if(preg_match('/^\w*image.*_id$/', $property))
				$field['type'] = 'image';
			elseif(preg_match('/^\w+_id$/', $property) || $property == 'id')
				$field['type'] = 'uint';
			elseif(preg_match('/order$/', $property))
				$field['type'] = 'int';
			elseif(preg_match('/^is_\w+$/', $property))
				$field['type'] = 'bool';
			elseif(preg_match('/^\w+_date$/', $property))
			{
				$field['type'] = 'date';
				$field['post_function'] = array('bors_time_date', 'load');
				$field['can_drop'] = true;
			}
			elseif(preg_match('/^\w+_ts$/', $field['name']))
			{
				$field['type'] = 'timestamp';
				$field['sql_function'] = 'UNIX_TIMESTAMP';
			}
			elseif(preg_match('/text/', $property))
				$field['type'] = 'text';
			elseif(preg_match('/description/', $property))
				$field['type'] = 'bbcode';
			elseif(preg_match('/^\w+$/', $property))
				$field['type'] = 'string';
			else
				bors_throw(ec('Неизвестное поле ').$property);
		}

		if(in_array($property, array('id', 'create_time', 'create_ts', 'modify_time', 'modify_ts', 'owner_id', 'last_editor_id', 'last_editor_ua', 'last_editor_ip')))
			set_def($field, 'is_editable', false);

		if(!empty(self::$default_field_names[$property]))
			set_def($field, 'title', ec(self::$default_field_names[$property]));

		return $field;
	}

	static function property_type_autodetect($property, &$info = array())
	{
		if(preg_match('/^\w+_id$/', $property) || $property == 'id')
			return $info['type'] = 'uint';

		if(preg_match('/(order|count)$/', $property))
			return $info['type'] = 'int';

		if(preg_match('/^(is_|has_|have_)\w+$/', $property))
			return $info['type'] = 'bool';

		if(preg_match('/^\w+_date$/', $property))
		{
			$info['post_function'] = array('bors_time_date', 'load');
			return $info['type'] = 'date';
		}

		if(preg_match('/text/', $property))
			return $info['type'] = 'text';

		if(preg_match('/^\w+$/', $property))
			return $info['type'] = 'string';

  		bors_throw(ec('Неизвестное поле ').$property);
	}

	static function all_fields($object, $only_editable = true)
	{
		//TODO: добавить touch в storage, чтобы при добавлении поля в автоматические классы и кеш перечитывался. Также с брос кеша класса добавить.
		$cache_name = 'class_'.($only_editable ? 'editable' : 'all').'_fields';
		$cached_data = $object->class_cache_data();
		if(!empty($cached_data[$cache_name]))
			return $cached_data[$cache_name];

		// Кеширование может быть сброшено из storage. При возможной замене менять сброс и там!
		// При добавлении нового поля класса, например.
		if($fields = global_key($gk = 'bors_lib_orm_class_fields-'.intval($only_editable), $object->class_name()))
			return $fields;

		$fields_array = array();
		foreach($object->get('fields', array()) as $db => $tables)
		{
			foreach($tables as $table => $fields)
			{
				foreach($fields as $property => $field)
				{
					if($field != '*no_defaults')
					{
						$field = array_merge(array(
							'db' => $db,
							'table' => $table,
						), self::field($property, $field));


						// Если у нас явно указан класс поля, то это прямое указание
						// для auto_objects()
						if(!empty($field['class']) && preg_match('/^(\w+)_id$/', $field['property'], $m))
							$GLOBALS['bors-orm-cache']['auto_objects_append'][$m[1]] = "{$field['class']}({$field['property']})";

						if(strpos($field['name'], '`') === false)
							$field['name'] = "`{$field['name']}`";

						if(!empty($field['sql_function']) && $field['sql_function'] == 'UNIX_TIMESTAMP')
							$field['sql_order_field'] = $field['name'];

						// UNIX_TIMESTAMP(`Date`) => UNIX_TIMESTAMP(`News`.`Date`)
						if(empty($field['sql_function']))
							$field['sql_tab_name'] = "`{$field['table']}`.{$field['name']}";
						else
							$field['sql_tab_name'] = preg_replace("/({$field['name']})/", "`{$field['table']}`.$1", $field['sql_name']);

						$fields_array[] = $field;
					}
				}
			}
		}

		foreach(array('inner', 'left') as $inner_type)
		{
			foreach($object->get("{$inner_type}_join_fields", array()) as $db => $tables)
			{
				foreach($tables as $table => $fields)
				{
					if(preg_match('/^(\w+)\(.+\)$/', $table, $m))
						$table = $m[1];

					foreach($fields as $property => $field)
					{
						if($field != '*no_defaults')
						{
							$field = array_merge(array(
								'db' => $db,
								'table' => $table,
							), self::field($property, $field));

//							if($field['name'] != 'id')
							// UNIX_TIMESTAMP(`Date`) => UNIX_TIMESTAMP(`News`.`Date`)
							if(empty($field['sql_function']))
								$field['sql_tab_name'] = "`{$field['table']}`.`{$field['name']}`";
							else
								$field['sql_tab_name'] = preg_replace("/(`{$field['name']}`)/", "`{$field['table']}`.$1", $field['sql_name']);

							$fields_array[] = $field;
						}
					}
				}
			}
		}

		set_global_key($gk, $object->class_name(), $fields_array);
		$object->set_class_cache_data($cache_name, $fields_array);
		return $fields_array;
	}

	static function all_field_names($object)
	{
		if(!is_object($object)) // Тогда это — имя класса
			$object = new $object(NULL); // Подставим пустышку

		$fields_array = array();

		foreach(get_class_methods($object->class_name()) as $name)
			if(preg_match('/^set_(\w+)$/', $name, $m))
				$fields_array[$m[1]] = $m[1];

//		print_r($object->fields());

		foreach($object->fields() as $db => $tables)
			foreach($tables as $table => $fields)
				foreach($fields as $property => $field)
				{
					$f = self::field($property, $field);
					$fields_array[$f['property']] = $f['name'];
					if(preg_match('/_date$/', $f['property']))
					{	//FIXME: Костыль для сохранения значений формата автодаты.
						$fields_array[$f['property'].'_day'] = true;
						$fields_array[$f['property'].'_month'] = true;
						$fields_array[$f['property'].'_year'] = true;
						$fields_array[$f['property'].'_hour'] = true;
						$fields_array[$f['property'].'_minute'] = true;
						$fields_array[$f['property'].'_seconds'] = true;
						$fields_array['time_vars'] = true;
					}
				}

		return $fields_array;
	}

	static function main_fields($object)
	{
		$class_name = $object->class_name();

		if($f = $object->class_cache_data($cache_key = 'main-fields-'.$class_name))
			return $f;

		$object->table_fields();	// Хак. Дёргаем, чтобы извлечь данные по автоматическим полям,
									// в частности — auto_objects из FOREIGN KEYS.

		$fields_array = array();

		$defaults = true;

		$properties_parsed = array();
		foreach($object->table_fields() as $property => $field)
		{
			if($field != '*no_defaults')
			{
				$f = self::field($property, $field);
				$properties_parsed[] = $f['property'];
				if($f['property'] == 'id')
					$defaults = false;
				$fields_array[] = $f;
			}
			else
				$defaults = false;
		}

		if($defaults)
		{
			$foo = array('is_editable' => false);
			array_unshift($fields_array, self::field('id', $foo));

			foreach(array(
				'modify_time' => array('name' => 'UNIX_TIMESTAMP(`modify_time`)', 'type' => 'timestamp', 'index' => true, 'is_editable' => false),
				'create_time' => array('name' => 'UNIX_TIMESTAMP(`create_time`)', 'type' => 'timestamp', 'index' => true, 'is_editable' => false),
				'owner_id' => array('is_editable' => false),
				'last_editor_id' => array('is_editable' => false)
			) as $property => $data)
			{
				$f = self::field($property, $data);
				if(!in_array($f['property'], $properties_parsed))
					$fields_array[] = $f;
			}
		}

		return $object->set_class_cache_data($cache_key, $fields_array);
	}

	static function fields($object)
	{
		$class_name = $object->class_name();
		if($fields = global_key('___fields', $class_name))
			return $fields;

		$fields_array = array();

		$table_fields = $object->get('table_fields');

		if(is_array($table_fields))
		{
			foreach($table_fields as $property => $field)
			{
				$field = self::field($property, $field);
				$fields_array[$field['property']] = $field;
			}
		}

		return set_global_key('___fields', $class_name, $fields_array);
	}

	static function property_to_field($class_name, $property)
	{
		$object = new $class_name(NULL);
		foreach(self::all_fields($object) as $f)
		{
			if($f['property'] == $property)
				return $f['sql_name'];
		}

		return NULL;
	}

	static function parse_property($class_name, $property)
	{
		$object = bors_foo($class_name);
//		$class_file = bors_class_loader::load($class_name);
//		$object->set_class_file($class_file);

		foreach(self::all_fields($object) as $f)
			if($f['property'] == $property)
				return $f;

		return NULL;
	}

	static function db_name($class_name)
	{
		$foo = new $class_name(NULL);
		$class_file = bors_class_loader::load($class_name);
		$foo->set_class_file($class_file);
		return object_property($foo, 'db_name');
	}

	static function table_name($class_name)
	{
		$foo = new $class_name(NULL);
		$class_file = bors_class_loader::load($class_name);
		$foo->set_class_file($class_file);
		return object_property($foo, 'table_name');
	}

	static function property_sign(&$property, $shift = false)
	{
		if(preg_match('/^([\+\-])(\w+)$/', $property, $m))
		{
			$sign = $m[1];
			if($shift)
				$property = $m[2];
		}
		else
			$sign = '';

		return $sign;
	}

	static function reverse_sign($property, $current_property)
	{
		$prop_sign = self::property_sign($property, true);
		$curr_sign = self::property_sign($current_property, true);

		if($property != $current_property)
			return $prop_sign.$property;

		if($curr_sign != '-')
			return '-'.$property;

		return $property;
	}
}
