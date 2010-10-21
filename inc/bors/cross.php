<?php

//TODO: Сделать автоочистку привязок к несуществующим объектам. В первую очередь - aviaport_images

function bors_get_cross_ids($object, $to_class = '', $dbh = NULL)
{
	if(!$dbh)
		$dbh = new driver_mysql(config('bors_core_db'));

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
		$dbh = new driver_mysql(config('bors_core_db'));

	if(empty($args['order']))
		$order = 'ORDER BY sort_order, object_id';
	else
		$order = 'ORDER BY '.$args['order'];

	if(empty($args['limit']))
		$limit = '';
	else
		$limit = "LIMIT ".intval($args['limit']);

	$where_to = $where_from = array();

	if($to_class)
	{
		bors_cross_where_cond('to_class', $to_class, $where_to);
		bors_cross_where_cond('from_class', $to_class, $where_from);
	}

	$where_to['from_class'] = $object->class_id();
	$where_to['from_id'] = $object->id();
	$where_from['to_class'] = $object->class_id();
	$where_from['to_id'] = $object->id();

	if(!empty($args['type_id']))
	{
		$where_to['type_id'] = $args['type_id'];
		$where_from['type_id'] = $args['type_id'];
	}

	$where_from = mysql_where_compile($where_from);
	$where_to   = mysql_where_compile($where_to  );


	$arr = $dbh->get_array("
		(SELECT to_class as class_id, to_id as object_id, sort_order, type_id, owner_id, was_moderated, target_create_time  FROM bors_cross $where_to)
		UNION
		(SELECT from_class as class_id, from_id as object_id, sort_order, type_id, owner_id, was_moderated, target_create_time  FROM bors_cross $where_from)
		$order
		$limit
	");

//	$arr = $dbh->select_array('bors_cross', 'to_class as class_id, to_id as object_id, sort_order, type_id', $where_to);
//	$arr = array_merge($arr, $dbh->select_array('bors_cross', 'from_class as class_id, from_id as object_id, sort_order, type_id', $where_from));

	$inits = array();
	foreach($arr as $x)
		@$inits[$x['class_id']][$x['object_id']] = $x['object_id'];

	foreach($inits as $class_id => $ids)
		$objs[$class_id] = objects_array($class_id, array('id IN' => array_keys($ids), 'by_id' => true));

	$object_iu = $object->internal_uri();
	$result = array();

	foreach($arr as $r)
	{
		$x = @$objs[$r['class_id']][$r['object_id']];
		if(empty($x))
		{
			if(config('bors_link.lost_auto_delete'))
			{
				debug_hidden_log('cross-errors', "Cross {$object->internal_uri()} to unknown object ".print_r($r, true));
				bors_remove_cross_pair($r['class_id'], $r['object_id'], $object->class_id(), $object->id(), $dbh);
			}
			continue;
		}

		$x_iu = $x->internal_uri();

		if($r['owner_id'] < 0 && !$r['was_moderated'])
			$r['type_id'] = -$r['type_id'];

		if($x_iu < $object_iu)
		{
			$bors_cross_types_map[$x_iu][$object_iu] = $r['type_id'];
//			$bors_cross_sort_orders[$x_iu][$object_iu] = $r['sort_order'];
		}
		else
		{
			$bors_cross_types_map[$object_iu][$x_iu] = $r['type_id'];
//			$bors_cross_sort_orders[$object_iu][$x_iu] = $r['sort_order'];
		}

		$x->set_sort_order($r['sort_order'], false);

		$result[$x->internal_uri()] = $x;
	}

	return array_values($result);
}

function bors_cross_type_id($x1, $x2)
{
	debug_hidden_log('__obsolete_need_rewrite', 'Call bors_cross_type_id!');
	return 0;

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

function bors_cross_where_cond($field, $cond, &$where)
{
	$yes = array();
	$no  = array();

	foreach(explode(',', $cond) as $c)
		if(preg_match('!^\-(.+)$!', $c, $m))
			$no[] = is_numeric($m[1]) ? $m[1] : class_name_to_id($m[1]);
		else
			$yes[] = is_numeric($c) ? $c : class_name_to_id($c);
	
//	$where = array();
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
		$dbh = new driver_mysql(config('bors_core_db'));

	if($from->class_id() > $to->class_id())
		list($from, $to) = array($to, $from);
	elseif($from->class_id() == $to->class_id() && $from->id() > $to->id())
		list($from, $to) = array($to, $from);

	if($from->class_id() == $to->class_id() && $from->id() == $to->id())
		return;

	$dbh->replace('bors_cross', array(
		'from_class' => $from->class_id(),
		'from_id' => $from->id(),
		'to_class' => $to->class_id(),
		'to_id' => $to->id(),
		'sort_order'	=> $order,
		'create_time' => time(),
		'modify_time' => time(),
	));
}

function bors_add_cross($from_class, $from_id, $to_class, $to_id, $order=0, $type_id = 0, $dbh = NULL, $ins_type = 'replace', $owner_id = NULL)
{
	if(!$dbh)
		$dbh = new driver_mysql(config('bors_core_db'));

	if(!is_numeric($from_class))
		$from_class = class_name_to_id($from_class);

	if(!is_numeric($to_class))
		$to_class = class_name_to_id($to_class);

	if($from_class == $to_class && $from_id == $to_id)
		return;

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

	$data = array(
		'type_id' => $type_id,
		'from_class' => $from_class,
		'from_id' => $from_id,
		'to_class' => $to_class,
		'to_id' => $to_id,
		'sort_order'	=> $order,
		'create_time' => time(),
		'modify_time' => time(),
		'owner_id' => $owner_id,
	);

	if($ins_type == 'ignore')
		$dbh->insert_ignore('bors_cross', $data);
	else
		$dbh->replace('bors_cross', $data);
}

function bors_remove_cross_pair($from_class, $from_id, $to_class, $to_id, $dbh = NULL)
{
	if(!$dbh)
		$dbh = new driver_mysql(config('bors_core_db'));

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
		$dbh = new driver_mysql(config('bors_core_db'));

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
