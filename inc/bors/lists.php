<?php

function bors_named_list_db($class_name, $zero_item = NULL, $where = array())
{
	$obj = object_new($class_name);

	$items_db = $obj->main_db_storage();
	$items_tab = $obj->main_table_storage();
	$db = new driver_mysql($items_db);


	$res = array();

	if(isset($zero_item))
		$res[0] = $zero_item;

	if(empty($where['order']))
		$where['order'] = 'title';

	foreach($db->select_array($items_tab, 'id, title', $where) as $x)
		$res[$x['id']] = $x['title'];

	return $res;
}

function bors_named_hierarchic_list_db($class_name, $zero_item = NULL)
{
	$obj = object_new($class_name);

	$items_db = $obj->main_db_storage();
	$items_tab = $obj->main_table_storage();
	$db = new driver_mysql($items_db);

	$res = array();

	if(isset($zero_item))
		$res[0] = $zero_item;

	$roots = array();
	$children = array();

	foreach($db->select_array($items_tab, 'id, title, parent_id', array('order' => 'parent_id, title')) as $x)
		if($x['parent_id'])
			$children[$x['parent_id']][$x['id']] = $x['title'];
		else
			$roots[$x['id']] = $x['title'];

//	print_d($children);

	foreach($roots as $root_id => $title)
	{
		$res[$root_id] = $title;
		if($list = @$children[$root_id])
			foreach($list as $child_id => $title)
				$res[$child_id] = str_repeat('&nbsp;', 4).$title;
	}
	
	return $res;
}
