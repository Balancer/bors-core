<?php

class bors_lib_orm
{
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
			// Это запись вида 'property' => 'fiels|post_function'
				$field = array('name' => $m[1], 'post_function' => $m[2]);
			else // просто строка вида 'property' => 'field',
				$field = array('name' => $field);
		}
		else // Описание — массив параметров
		{
			if(empty($field['name']))
				$field['name'] = defval($field, 'field', $property);

			$field['sql_name'] = $field['name'];

			if(preg_match('!^(\w+)\(`(\w+)`\)$!', $field['name'], $m))
			{
				$field['name'] = $m[2];
				$field['sql_function'] = $m[1];
			}
		}

		$field['property'] = $property;

		if(empty($field['type']))
		{
			if(preg_match('/^\w+_id$/', $property) || $property == 'id')
				$field['type'] = 'uint';
			elseif(preg_match('/^is_\w+$/', $property))
				$field['type'] = 'bool';
			elseif(preg_match('/^\w+_date$/', $property))
				$field['type'] = 'date';
			elseif(preg_match('/text/', $property))
				$field['type'] = 'text';
			elseif(preg_match('/^\w+$/', $property))
				$field['type'] = 'string';
			else
				bors_throw(ec('Неизвестное поле ').$property);
		}

		return $field;
	}

	static function all_fields($object, $only_editable = true)
	{
		if($fields = global_key($gk = __CLASS__.'-'.$only_editable, $object->class_name()))
			return $fields;

		$fields_array = array();
		foreach($object->fields() as $db => $tables)
		{
			foreach($tables as $table => $fields)
				foreach($fields as $property => $field)
				{
					if($field != '*no_defaults')
					{
						$field = self::field($property, $field);
//					if($field['name'] != 'id')
							$fields_array[] = $field;
					}
				}
		}

		return set_global_key($gk, $object->class_name(), $fields_array);
	}

	static function all_field_names($object)
	{
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
		if($fields = global_key('___main_fields', $class_name))
			return $fields;

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
			debug_hidden_log('__defaults', "Found defaults for {$object->debug_title()}");

			$x = 'id';
			array_unshift($fields_array, self::field(0, $x));

			foreach(array(
				'modify_time' => array('name' => 'UNIX_TIMESTAMP(`modify_time`)', 'type' => 'timestamp', 'index' => true),
				'create_time' => array('name' => 'UNIX_TIMESTAMP(`create_time`)', 'type' => 'timestamp', 'index' => true),
				'owner_id',
				'last_editor_id'
			) as $property => $data)
			{
				$f = self::field($property, $data);
				if(!in_array($f['property'], $properties_parsed))
					$fields_array[] = $f;
			}
		}

		return set_global_key('___main_fields', $class_name, $fields_array);
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
		$object = new $class_name(NULL);
		foreach(self::all_fields($object) as $f)
			if($f['property'] == $property)
				return $f;

		return NULL;
	}
}
