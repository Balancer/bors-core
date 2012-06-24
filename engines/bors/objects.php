<?php

// Создаёт пустой неинициализированный объект
/*function object_new($class_name, $id = NULL)
{
	$obj = new $class_name($id);
	return $obj;
}*/

function bors_object_title_autoinc($class_name, $title)
{
	if($obj = objects_first($class_name, array('title' => $title)))
		return $obj;

	return object_new_instance($class_name, array('title' => $title));
}

function bors_title_to_autoinc_id(&$data, $name_field, $id_field, $class_name)
{
	if(empty($data[$id_field]))
	{
		if(empty($data[$name_field]))
			return bors_message(ec('Не указан ') . call_user_func(array($class_name, 'class_title')));

		$x = bors_object_title_autoinc($class_name, $data[$name_field]);
		if(!$x)
		{
			debug_hidden_log('new-record-error', "bors_title_to_autoinc_id(".print_r($data,true).", $name_field, $id_field, $class_name)");
			return bors_message(ec('Ошибка создания новой записи'));
		}

		$data[$id_field] = $x->id();
	}
	unset($data[$name_field]);

	return false;
}
