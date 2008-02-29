<?php

function class_name_to_id($object)
{
	if(is_object($object))
		$class_name = get_class($object);
	else
		$class_name = "$object";

	if(strlen($class_name) > 64)
	{
		echo 0/0;
		exit("Too long class name: '$class_name'");
	}

	$db = &new DataBase(config('main_bors_db'));
	$class_id = $db->get("SELECT id FROM bors_class_names WHERE name = '".addslashes($class_name)."'");

	if($class_id)
		return $class_id;
			
	$db->insert('bors_class_names', array('name' => $class_name));
	return $db->last_id();
}

function class_id_to_name($class_id)
{
	$db = &new DataBase(config('main_bors_db'));
	return $db->get("SELECT name FROM bors_class_names WHERE id = ".intval($class_id)."");
}
