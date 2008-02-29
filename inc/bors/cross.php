<?php

function bors_get_cross($object, $to_class = '', $dbh = NULL)
{
	if(!$dbh)
		$dbh = &new driver_mysql('WWW');

	if($to_class)
	{
		if(!is_numeric($to_class))
			$to_class = class_name_to_id($to_class);

		$to_class = " AND to_class = {$to_class} ";
	}

	$result = array();
	$dbh->query("SELECT to_class, to_id FROM bors_cross WHERE from_class={$object->class_id()} AND from_id=".intval($object->id())." {$to_class} ORDER BY `order`, to_id");
				
	while($row = $dbh->fetch_row())
		$result[] = $to_class ? $row['to_id'] : array($row['to_class'], $row['to_id']);

	return $result;
}

function bors_get_cross_objs($object, $to_class = '', $dbh = NULL)
{
	if(!$dbh)
		$dbh = &new driver_mysql('WWW');

	if($to_class)
	{
		if(!is_numeric($to_class))
			$to_class = class_name_to_id($to_class);

		$to_class = " AND to_class = {$to_class} ";
	}

	$result = array();
	$dbh->query("SELECT to_class, to_id FROM bors_cross WHERE from_class={$object->class_id()} AND from_id=".intval($object->id())." {$to_class} ORDER BY `order`, to_id");
				
	while($row = $dbh->fetch_row())
		$result[] = object_load($row['to_class'], $row['to_id']);

	return $result;
}

function bors_add_cross_obj($from, $to, $order=0, $dbh = NULL)
{
	if(!$dbh)
		$dbh = &new driver_mysql('WWW');

	$dbh->replace('bors_cross', array(
		'from_class' => $from->class_id(),
		'from_id' => $from->id(),
		'to_class' => $to->class_id(),
		'to_id' => $to->id(),
		'order'	=> $order
	));
}

function bors_add_cross($from_class, $from_id, $to_class, $to_id, $order=0, $dbh = NULL)
{
	if(!$dbh)
		$dbh = &new driver_mysql('WWW');

	if(!is_numeric($from_class))
		$from_class = class_name_to_id($from_class);

	if(!is_numeric($to_class))
		$to_class = class_name_to_id($to_class);

	$dbh->replace('bors_cross', array(
		'from_class' => $from_class,
		'from_id' => $from_id,
		'to_class' => $to_class,
		'to_id' => $to_id,
		'order'	=> $order
	));
}
