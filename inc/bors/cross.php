<?php

function bors_get_cross_ids($object, $to_class = '', $dbh = NULL)
{
	if(!$dbh)
		$dbh = &new driver_mysql(config('bors_core_db'));

	if($to_class)
	{
		if(!is_numeric($to_class))
			$to_class = class_name_to_id($to_class);

		$to_class_where = " AND to_class = {$to_class} ";
		$from_class_where = " AND from_class = {$to_class} ";
	}
	else
	{
		$to_class_where = '';
		$from_class_where = '';
	}
	
	$result = array();
	$dbh->query("SELECT to_class, to_id FROM bors_cross WHERE from_class={$object->class_id()} AND from_id=".intval($object->id())." {$to_class_where} ORDER BY `order`, to_id");
				
	while($row = $dbh->fetch_row())
		$result[] = $to_class ? $row['to_id'] : array($row['to_class'], $row['to_id']);

	$dbh->query("SELECT from_class, from_id FROM bors_cross WHERE to_class={$object->class_id()} AND to_id=".intval($object->id())." {$from_class_where} ORDER BY `order`, from_id");
				
	while($row = $dbh->fetch_row())
		$result[] = $to_class ? $row['from_id'] : array($row['from_class'], $row['from_id']);

	return $result;
}

function bors_cross_object_init($row)
{
	$obj = object_load($row['class_id'], $row['object_id']);
	$obj->set_sort_order($row['sort_order'], false);
	return $obj;
}

function bors_get_cross_objs($object, $to_class = '', $dbh = NULL)
{
	if(!$dbh)
		$dbh = &new driver_mysql(config('bors_core_db'));

	if($to_class)
	{
		$to_class_where = bors_cross_where_cond('to_class', $to_class);
		$from_class_where = bors_cross_where_cond('from_class', $to_class);
	}
	else
	{
		$to_class_where = '';
		$from_class_where = '';
	}

	$result = array();

	$dbh->query("SELECT to_class as class_id, to_id as object_id, `order` as sort_order FROM bors_cross WHERE from_class={$object->class_id()} AND from_id=".intval($object->id())." {$to_class_where} ORDER BY `order`, to_id");
				
	while($row = $dbh->fetch_row())
		$result[] = bors_cross_object_init($row);

	$dbh->query("SELECT from_class as class_id, from_id as object_id, `order` as sort_order FROM bors_cross WHERE to_class={$object->class_id()} AND to_id=".intval($object->id())." {$from_class_where} ORDER BY `order`, from_id");
				
	while($row = $dbh->fetch_row())
		$result[] = bors_cross_object_init($row);
	
	return $result;
}

function bors_cross_where_cond($field, $cond)
{
	$yes = array();
	$no  = array();

	foreach(split(',', $cond) as $c)
		if(preg_match('!^\-(.+)$!', $c, $m))
			$no[] = is_numeric($m[1]) ? $m[1] : class_name_to_id($m[1]);
		else
			$yes[] = is_numeric($c) ? $c : class_name_to_id($c);
	
	$where = array();
	if($yes)
		$where[] = $field . (count($yes) > 1 ? ' IN ('.join(',', $yes).')' : '='.$yes[0]);
	if($no)
		$where[] = $field . (count($no) > 1 ? ' NOT IN ('.join(',', $yes).')' : '<>'.$no[0]);

	if($where)
		return 'AND '.join(' AND ', $where);
	else
		return '';
}

function bors_add_cross_obj($from, $to, $order=0, $dbh = NULL)
{
	if(!$dbh)
		$dbh = &new driver_mysql(config('bors_core_db'));

	if($from->class_id() > $to->class_id())
		list($from, $to) = array($to, $from);
	elseif($from->class_id() == $to->class_id() && $from->id() > $to->id())
		list($from, $to) = array($to, $from);
	
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
		$dbh = &new driver_mysql(config('bors_core_db'));

	if(!is_numeric($from_class))
		$from_class = class_name_to_id($from_class);

	if(!is_numeric($to_class))
		$to_class = class_name_to_id($to_class);

	if($from_class > $to_class)
	{
		list($from_class, $to_class) = array($to_class, $from_class);
		list($from_id, $to_id) = array($to_id, $from_id);
	}
	elseif($from_class == $to_class && $from_id > $to_id)
	{
		list($from_class, $to_class) = array($to_class, $from_class);
		list($from_id, $to_id) = array($to_id, $from_id);
	}

	$dbh->replace('bors_cross', array(
		'from_class' => $from_class,
		'from_id' => $from_id,
		'to_class' => $to_class,
		'to_id' => $to_id,
		'order'	=> $order
	));
}

function bors_remove_cross_pair($from_class, $from_id, $to_class, $to_id, $dbh = NULL)
{
	if(!$dbh)
		$dbh = &new driver_mysql(config('bors_core_db'));

	if(!is_numeric($from_class))
		$from_class = class_name_to_id($from_class);

	if(!is_numeric($to_class))
		$to_class = class_name_to_id($to_class);

	$dbh->delete('bors_cross', array(
		'from_class=' => $from_class,
		'from_id=' => $from_id,
		'to_class=' => $to_class,
		'to_id=' => $to_id,
	));

	$dbh->delete('bors_cross', array(
		'from_class=' => $to_class,
		'from_id=' => $to_id,
		'to_class=' => $from_class,
		'to_id=' => $from_id,
	));
}

function bors_remove_cross_to($to_class, $to_id, $dbh = NULL)
{
	if(!$dbh)
		$dbh = &new driver_mysql(config('bors_core_db'));

	if(!is_numeric($to_class))
		$to_class = class_name_to_id($to_class);

	$dbh->delete('bors_cross', array(
		'to_class=' => $to_class,
		'to_id=' => $to_id,
	));

	$dbh->delete('bors_cross', array(
		'from_class=' => $to_class,
		'from_id=' => $to_id,
	));
}
