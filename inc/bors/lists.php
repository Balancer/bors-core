<?php

function bors_named_list_db($class_name, $zero_item = NULL)
{
	$obj = object_new($class_name);

	$items_db = $obj->main_db_storage();
	$items_tab = $obj->main_table_storage();
	$db = new driver_mysql($item_db);
	
	
	$res = array();
	
	if(isset($zero_item))
		$res[0] = $zero_item;
	
	foreach($db->select_array($items_tab, 'id, title', array('order' => 'title')) as $x)
		$res[$x['id']] = $x['title'];
	
	return $res;
}
