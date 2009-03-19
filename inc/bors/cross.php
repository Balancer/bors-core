<?php

//TODO: Сделать автоочистку привязок к несуществующим объектам. В первую очередь - aviaport_images

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
	$dbh->query("SELECT to_class, to_id FROM bors_cross WHERE from_class={$object->class_id()} AND from_id=".intval($object->id())." {$to_class_where} ORDER BY `sort_order`, to_id");

	while($row = $dbh->fetch_row())
		$result[] = $to_class ? $row['to_id'] : array($row['to_class'], $row['to_id']);

	$dbh->query("SELECT from_class, from_id FROM bors_cross WHERE to_class={$object->class_id()} AND to_id=".intval($object->id())." {$from_class_where} ORDER BY `sort_order`, from_id");

	while($row = $dbh->fetch_row())
		$result[] = $to_class ? $row['from_id'] : array($row['from_class'], $row['from_id']);

	return $result;
}

function bors_cross_object_init($row)
{
	$obj = object_load($row['class_id'], $row['object_id']);
	if($obj)
		$obj->set_sort_order($row['sort_order'], false);
	return $obj;
}

function bors_get_cross_objs($object, $to_class = '', $dbh = NULL, $args = array())
{
	global $bors_cross_types_map, $bors_cross_sort_orders;

	if(!$object)
	{
		debug_hidden_log('cross-errors', 'Try to get cross for empty object. [' . ($to_class ? "to_class={$to_class}" : ''). ']');
		return array();
	}

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
	$limit = empty($args['limit']) ? '' : 'LIMIT '.addslashes($args['limit']);
	$order = empty($args['sort_order']) ? 'ORDER BY `sort_order`, to_id' : addslashes(mysql_order_compile($args['sort_order']));

	$dbh->query("SELECT to_class as class_id, to_id as object_id, sort_order, type_id FROM bors_cross WHERE from_class={$object->class_id()} AND from_id=".intval($object->id())." {$to_class_where} $order $limit");

	$object_iu = $object->internal_uri();
	while($row = $dbh->fetch_row())
		if($x = bors_cross_object_init($row))
		{
			$x_iu = $x->internal_uri();
			if($x_iu < $object_iu)
			{
				$bors_cross_types_map[$x_iu][$object_iu] = $row['type_id'];
				$bors_cross_sort_orders[$x_iu][$object_iu] = $row['sort_order'];
			}
			else
			{
				$bors_cross_types_map[$object_iu][$x_iu] = $row['type_id'];
				$bors_cross_sort_orders[$object_iu][$x_iu] = $row['sort_order'];
			}
			$result[] = $x;
		}
		else
		{
			bors_remove_cross_pair($row['class_id'], $row['object_id'], $object->class_id(), $object->id());
			debug_hidden_log('cross-errors', "Empty cross ".print_r($row, true)." with {$object} [class_id = {$object->class_id()}]");
		}
		
	$dbh->query("SELECT from_class as class_id, from_id as object_id, sort_order, type_id FROM bors_cross WHERE to_class={$object->class_id()} AND to_id=".intval($object->id())." {$from_class_where} $order $limit");

	while($row = $dbh->fetch_row())
		if($x = bors_cross_object_init($row))
		{
			$x_iu = $x->internal_uri();
			if($x_iu < $object_iu)
			{
				$bors_cross_types_map[$x_iu][$object_iu] = $row['type_id'];
				$bors_cross_sort_orders[$x_iu][$object_iu] = $row['sort_order'];
			}
			else
			{
				$bors_cross_types_map[$object_iu][$x_iu] = $row['type_id'];
				$bors_cross_sort_orders[$object_iu][$x_iu] = $row['sort_order'];
			}
			$result[] = $x;
		}
		else
		{
			debug_hidden_log('cross-errors', "Empty cross ".print_r($row, true)." with {$object} [class_id = {$object->class_id()}]");
			bors_remove_cross_pair($row['class_id'], $row['object_id'], $object->class_id(), $object->id());
		}

	return $result;
}

function bors_cross_type_id($x1, $x2)
{
	global $bors_cross_types_map;
	$x1_iu = $x1->internal_uri();
	$x2_iu = $x2->internal_uri();
	if($x1_iu > $x2_iu)
	{
		$x = $x1_iu;
		$x1_iu = $x2_iu;
		$x2_iu = $x;
	}

	if(empty($bors_cross_types_map[$x1_iu][$x2_iu]))
		bors_get_cross_objs($x1, $x2->class_name());

	return intval(@$bors_cross_types_map[$x1_iu][$x2_iu]);
}

function bors_cross_sort_order($x1, $x2)
{
	global $bors_cross_sort_orders;
	$x1_iu = $x1->internal_uri();
	$x2_iu = $x2->internal_uri();
	if($x1_iu > $x2_iu)
	{
		$x = $x1_iu;
		$x1_iu = $x2_iu;
		$x2_iu = $x;
	}

	if(empty($bors_cross_sort_orders[$x1_iu][$x2_iu]))
		bors_get_cross_objs($x1, $x2->class_name());

	return intval(@$bors_cross_sort_orders[$x1_iu][$x2_iu]);
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
		'sort_order'	=> $order
	));
}

function bors_add_cross($from_class, $from_id, $to_class, $to_id, $order=0, $type_id = 0, $dbh = NULL)
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
		'type_id' => $type_id,
		'from_class' => $from_class,
		'from_id' => $from_id,
		'to_class' => $to_class,
		'to_id' => $to_id,
		'sort_order'	=> $order
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
