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
			$field = array('name' => $field);
		elseif(empty($field['name']))
			$field['name'] = $property;

		if(preg_match('/^\w+_id$/', $property) || $property == 'id')
			$field['type'] = 'int';
		elseif(preg_match('/^is_\w+$/', $property))
			$field['type'] = 'bool';
		elseif(preg_match('/^\w+_date$/', $property))
			$field['type'] = 'date';
		elseif(preg_match('/^\w+$/', $property))
			$field['type'] = 'string';
		else
			bors_throw(ec('Неизвестное поле ').$property);

		return $field;
	}
}
