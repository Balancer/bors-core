<?php

function mysql_where_compile($conditions_array)
{
//	if(isset($conditions_array['where']))
//		$conditions_array = $conditions_array['where'];

	if(isset($conditions_array['where']))
		$conditions_array = $conditions_array['where'];

	if(empty($conditions_array))
		return '';
	
	$where = array();
	foreach($conditions_array as $field_cond => $value)
	{

		$value = str_replace('%ID%', '%MySqlStorageOID%', $value);
//		echo "$field_cond  $value<br/>\n";

		if(preg_match('! IN$!', $field_cond))
		{
			if(is_array($value))
				$value = join(',', array_map('addslashes', $value));

			$where[] = mysql_bors_join_parse($field_cond) . '(' . $value . ')';
		}
		elseif(is_numeric($field_cond))
			$where[] = $value;
		elseif(preg_match('!^\w+$!', $field_cond))
			$where[] = $field_cond . '=\'' . addslashes($value) . '\'';
		else
			$where[] = $field_cond . '\'' . addslashes($value) . '\'';
	}
	
	return 'WHERE '.join(' AND ', $where);
}

function mysql_order_compile($order_list)
{
//	if(isset($order_list['order']))
//		$order_list = $order_list['order'];
		
	if(empty($order_list))
		return '';
		
	$order = array();
	foreach(split(',', $order_list) as $o)
	{
		if(preg_match('!^\-(.+)$!', $o, $m))
			$order[] = $m[1].' DESC';
		else
			$order[] = $o;
	}

	return 'ORDER BY '.join(',', $order);
}

function mysql_limits_compile($args)
{
	if(!empty($args['limit']))
		return "LIMIT {$args['limit']}";

	if(empty($args['page']) && empty($args['per_page']))
		return "";
		
	$page = intval(@$args['page']);
	$per_page = @$args['per_page'];
	$start = (max($page,1)-1)*intval($per_page);
	
	return 'LIMIT '.$start.','.$per_page;
}

function bors_class_field_to_db($class, $field = NULL)
{
	if(!class_exists($class))
		return $field ? $class.'.'.$field : $class;


	$table	 = call_user_func(array($class, 'main_table_storage'));
	$fields	 = call_user_func(array($class, 'main_table_fields'));

	return $field ? $table.'.'.$fields[$field] : $table;
}

function mysql_bors_join_parse($join)
{
	$join = preg_replace('!(\w+)\s+ON\s+!e', 'bors_class_field_to_db("$1")." ON "', $join);
	$join = preg_replace('!(\w+)\.(\w+)!e', 'bors_class_field_to_db("$1", "$2")', $join);
//	echo "$join<br/>";
	return $join;
}

function mysql_args_compile($args)
{
	$join = '';
	if(!empty($args['inner_join']))
	{
		$join = array();
		if(is_array($args['inner_join']))
		{
			foreach($args['inner_join'] as $j)
				$join[] = 'INNER JOIN '.mysql_bors_join_parse($j);
		}
		else
			$join[] = 'INNER JOIN '.mysql_bors_join_parse($args['inner_join']);
		
		$join = join(' ', $join);
		
		unset($args['inner_join']);
	}

	$limit = mysql_limits_compile($args);
	unset($args['limit']);
	unset($args['page']);
	unset($args['per_page']);

	$order = "";
	if(!empty($args['order']))
	{
		$order = mysql_order_compile($args['order']);

		unset($args['order']);
	}

	$group = "";
	if(!empty($args['group']))
	{
		$group = "GROUP BY {$args['group']}";
		unset($args['group']);
	}
	
	if(empty($args['where']))
		$where = mysql_where_compile($args);
	else
		$where = mysql_where_compile($args['where']);
	
	return "{$join} {$where} {$group} {$order} {$limit}";
}

function make_id_field($table, $id_field, $oid = '%MySqlStorageOID%')
{
	if($table)
		$table = "{$table}.";
	
	if(strpos($id_field, '=') === false)
		return "{$table}{$id_field} = '".addslashes($oid)."'";

	if(strpos($id_field, ' ') === false)
	{
		$out =  preg_replace("!([\w`\.]+)=([\w\`\.]+)!", "{$table}$1=$2", $id_field);
		$out =  preg_replace("!(\w+)='(\w+)'!", "{$table}$1='$2'", $out);
	}
	else
	{
		$out =  str_replace('%TABLE%.', $table, $id_field);
		$out =  str_replace('%ID%', addslashes($oid), $out);
	}
		
	return $out;
}
