<?php

// Создаёт пустой неинициализированный объект
function object_new($class_name, $id = NULL)
{
	$obj = new $class_name($id);
	return $obj;
}
