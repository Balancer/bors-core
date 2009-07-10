<?php

function mysql_where_compile($conditions_array, $class='', $was_joined = true)
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

		$w = false;
		if(preg_match('! (NOT )?IN$!', $field_cond))
		{
			if(is_array($value))
				$value = "'".join("','", array_map('addslashes', $value))."'";

			if($value)
				$w = mysql_bors_join_parse($field_cond) . '(' . $value . ')';
			else
				$w = "0";
		}
		elseif(is_numeric($field_cond)) // Готовое условие
			$w = $value;
		elseif(preg_match('!^\w+$!', $field_cond))
			$w = $field_cond . '=\'' . addslashes($value) . '\'';
		elseif(preg_match('!^int (\w+)$!', $field_cond, $m))
			$w = $m[1] . '=' . $value;
		elseif(preg_match('!^raw (\w+)$!', $field_cond, $m))
			$w = $m[1] . '=' . $value;
		else
			$w = $field_cond . '\'' . addslashes($value) . '\'';
		
//		echo "$w => ".mysql_bors_join_parse($w)."<br/>";
		if($w)
			$where[] = mysql_bors_join_parse($w, $class, $was_joined);
	}
	
	return 'WHERE '.join(' AND ', $where);
}

function mysql_order_compile($order_list)
{
//	if(isset($order_list['order']))
//		$order_list = $order_list['order'];
		
	if(empty($order_list))
		return '';

	$order_list = mysql_bors_join_parse($order_list);

	$order = array();
	foreach(explode(',', $order_list) as $o)
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

function array_smart_expand(&$array)
{
	$result = array();
	if(!is_array($array))
		return $result;
		
	foreach($array as $key => $value)
		if(is_numeric($key))
			$result[$value] = $value;
		else
			$result[$key] = $value;
	
	return $result;
}

function bors_class_field_to_db($class, $field = NULL, $was_joined = true)
{
	if(is_object($class))
	{
		$table	 = $class->main_table();
		$fields	 = array_smart_expand($class->main_table_fields());
	}
	else
	{
		if(!class_exists($class))
			return $field ? $class.'.'.$field : $class;

		$class = new $class(NULL);

		if(method_exists($class, 'has_smart_field'))
		{
			$x = $class->has_smart_field($field); // array($r_db, $r_table, $r_id_field, $r_db_field);
//		$x = call_user_func(array($class, 'has_smart_field'), array($field)); // array($r_db, $r_table, $r_id_field, $r_db_field);
			$table	 = @$x[1];
			if(empty($table))
				$class->main_table();
//		echo "$class: $table, {$x[1]}<br/>";
			$fields	 = array_smart_expand($class->main_table_fields());
		}
	}


	if(empty($field))
		return $table;

	$f = $field;
	if(!empty($fields[$field]))
		if(preg_match('!^(.+)\|.+!', $f = $fields[$field], $m))
			$f = $m[1];

	if(!$was_joined && $table == $class->main_table())
		$table = '';

	if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
		return $m[1].'('.($table ? $table.'.' : '') . $m[2] .')';
	else
		return ($table ? $table.'.' : '') . $f;
}

function mysql_bors_join_parse($join, $class_name='', $was_joined = true)
{
	$join = preg_replace('!(\w+)\s+ON\s+!e', 'bors_class_field_to_db("$1")." ON "', $join);
	//TODO: мистика. Разобраться с ошибкой в http://balancer.ru/support/2009/07/t67868--Novaya-vozmozhnost~-v-driver_mysql.2967.html при чистых \w+
	$join = preg_replace('!([a-z]\w+)\.([a-z]\w+)!e', 'bors_class_field_to_db("$1", "$2")', $join);
	if($class_name)
		$join = preg_replace('!^(\w+)(\s*(=|>|<))!e', 'bors_class_field_to_db("'.$class_name.'", "$1", '.($was_joined ? 1 : 0).')."$2"', $join);
	return $join;
}

function mysql_args_compile($args, $class='')
{
	$join = array();
	if(!empty($args['inner_join']))
	{
		if(is_array($args['inner_join']))
		{
			foreach($args['inner_join'] as $j)
				$join[] = 'INNER JOIN '.mysql_bors_join_parse($j);
		}
		else
			$join[] = 'INNER JOIN '.mysql_bors_join_parse($args['inner_join']);
		
		unset($args['inner_join']);
	}

	if(!empty($args['left_join']))
	{
		if(is_array($args['left_join']))
		{
			foreach($args['left_join'] as $j)
				$join[] = 'LEFT JOIN '.mysql_bors_join_parse($j);
		}
		else
			$join[] = 'LEFT JOIN '.mysql_bors_join_parse($args['left_join']);
		
		unset($args['left_join']);
	}

	$join = join(' ', $join);

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

	$having = '';
	if(!empty($args['having']))
	{
		$having= "HAVING {$args['having']}";
		unset($args['having']);
	}
	
	if(empty($args['where']))
		$where = mysql_where_compile($args, $class, $join);
	else
		$where = mysql_where_compile($args['where']);
	
	return "{$join} {$where} {$group} {$having} {$order} {$limit}";
}

function make_id_field($table, $id_field, $oid = '%MySqlStorageOID%')
{
	if($table)
		$table = "{$table}.";
	
	if(strpos($id_field, '=') === false)
		return "{$table}{$id_field} = '".addslashes($oid)."'";

//	echo "make_id_field($table, $id_field, $oid)<br/>";

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
