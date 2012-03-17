<?php

bors_function_include('cache/global_key');
bors_function_include('cache/set_global_key');

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

		$field['property'] = $property;

		if(empty($field['type']))
		{
			if(preg_match('/^\w+_id$/', $property) || $property == 'id')
				$field['type'] = 'uint';
			elseif(preg_match('/order$/', $property))
				$field['type'] = 'int';
			elseif(preg_match('/^is_\w+$/', $property))
				$field['type'] = 'bool';
			elseif(preg_match('/^\w+_date$/', $property))
			{
				$field['type'] = 'date';
				$field['post_function'] = array('bors_time_date', 'load');
			}
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
			{
				foreach($fields as $property => $field)
				{
					if($field != '*no_defaults')
					{
						$field = array_merge(array(
							'db' => $db,
							'table' => $table,
						), self::field($property, $field));
//					if($field['name'] != 'id')
							$fields_array[] = $field;
					}
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

	static function get_notation($object, $name)
	{
		// Парсим файл класса на предмет @-нотаций
		if(!($class_file = $object->class_file()))
			return false;

		if(!($class_source = file_get_contents($class_file)))
			return false;

		if(preg_match("!^\s*@object:\s*$name\s*=\s*(\w+)\((\w+)\)\s*$!m", $class_source, $m))
			return bors_load($m[1], $object->get($m[2]));

		if(preg_match("!^\s*@$name\s*=(.+)*$!m", $class_source, $m))
			return trim($m[1]);

		return false;
	}

	static function get_yaml_notation($object, $name)
	{
		// Парсим файл класса на предмет YAML-нотаций
		if(is_null(@$object->attr['__yaml_notations']))
		{
			$object->attr['__yaml_notations'] = array();
			if($class_file = $object->class_file())
				if($class_source = file_get_contents($class_file))
					if(preg_match_all("!^/\*\*(.+?)^\*/!ms", $class_source, $match, PREG_SET_ORDER))
						foreach($match as $m)
							if(is_array($data = bors_data_yaml::parse(trim($m[1]), true)))
								$object->attr['__yaml_notations'] = array_merge($object->attr['__yaml_notations'], $data);
		}

		if($properties = @$object->attr['__yaml_notations']['properties'])
		{
			foreach($properties as $desc)
			{
				// default_image2 = aviaport_image(default_image_id)
				if(preg_match('!^(\w+)\s*=\s*(\w+)\((\w+)\)$!', trim($desc), $m) && ($name == $m[1]))
					return $object->attr[$name] = bors_load($m[2], $object->get($m[3]));
			}
		}

		return false;
	}
}
