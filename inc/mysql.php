<?php

function mysql_where_compile($conditions_array, $class='', $was_joined = true)
{
	if(isset($conditions_array['where']))
		$conditions_array = $conditions_array['where'];

	if(empty($conditions_array))
		return '';

	$where = array();

	if($raw_conditions = popval($conditions_array, '*raw_conditions'))
		$where = array_merge($where, $raw_conditions);

	foreach($conditions_array as $field_cond => $value)
	{
		$value = str_replace('%ID%', '%MySqlStorageOID%', $value);

		$w = false;
		if(preg_match('! (NOT )?IN$!', $field_cond))
		{
			if(is_array($value))
				$value = "'".join("','", array_map('addslashes', $value))."'";

			if($value)
				$w = mysql_bors_join_parse($field_cond, $class) . '(' . $value . ')';
			else
				$w = "0";
		}
		elseif(preg_match('! BETWEEN$!', $field_cond))
		{
			$w = mysql_bors_join_parse($field_cond, $class) . ' ' . $value[0] . ' AND ' . $value[1];
		}
		elseif(preg_match('! LIKE$!', $field_cond))
		{
			$w = mysql_bors_join_parse($field_cond, $class) . ' "%' . addslashes($value) . '%"';
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

		if($w)
		{
			$w = mysql_bors_join_parse($w, $class, $was_joined);
			// Некоторая частая оптимизация
			$w = bors_storage_mysql::condition_optimize($w);
			$where[] = $w;
		}
	}

	return $where ? 'WHERE '.join(' AND ', $where) : '';
}

function mysql_order_compile($order_list, $class_name = false)
{
	if(empty($order_list))
		return '';

	$order = array();
	foreach(explode(',', $order_list) as $field_name)
	{
		$dir = "";
		if(preg_match('!^\-(.+)$!', $field_name, $m))
		{
			$field_name = $m[1];
			$dir = ' DESC';
		}

		if(!preg_match('/`/', $field_name))
			$field_name = mysql_bors_join_parse($field_name, $class_name, true, true);

		$order[] = $field_name.$dir;
	}

	return 'ORDER BY '.join(',', $order);
}

function mysql_limits_compile(&$args)
{
	if($limit = popval($args, '*limit'))
	{
		if(is_array($limit))
			return "LIMIT ".intval($limit[0]).", ".intval($limit[1]);
		else
			return "LIMIT ".intval($limit);
	}

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

function bors_class_field_to_db($class, $property = NULL, $was_joined = true, $for_order = false)
{
//	if(1 || (config('is_debug') && $property=='create_time')) echo "<i>bors_class_field_to_db</i>($class, $property, $was_joined) <br/>\n";
	if(!$class)
		return $property;

	if(!is_object($class))
	{
		if(!class_exists($class))
			return $property ? $class.'.'.$property : $class;

		$class = new $class(NULL);
	}

	if($f = bors_lib_orm::parse_property($class->class_name(), $property))
	{
		if($for_order && ($x = @$f['sql_order_field']))
			return $x;

		if($was_joined) // Если это JOIN, то возвращаем полное имя поля, с таблицей
			return $f['sql_tab_name'];
		else // Иначе возвращаем просто имя поля.
			return $f['sql_name'];
	}

	if(!$property)
		return $class->table_name();

	return $property;
}

function mysql_bors_join_parse($join, $class_name='', $was_joined = true, $for_order = false)
{
	$join = preg_replace_callback('!(\w+)\s+ON\s+!', function($m) { return bors_class_field_to_db($m[1]).' ON ';}, $join);
	$join = preg_replace_callback('!^(\w+)\.(\w+)$!', function($m) { return bors_class_field_to_db($m[1], $m[2]);}, $join);
	$join = preg_replace_callback('!(\w+)\.(\w+)\s*(=|>|<)!', function($m) { return bors_class_field_to_db($m[1], $m[2]).$m[3];}, $join);
	$join = preg_replace_callback('!(\w+)\.(\w+)(\s+IN)!', function($m) { return bors_class_field_to_db($m[1], $m[2]).$m[3];}, $join);
	$join = preg_replace_callback('!(\w+)\.(\w+)(\s+BETWEEN\s+\S+\s+AND\s+\S+)!', function($m) { return bors_class_field_to_db($m[1], $m[2]).$m[3];}, $join);
//	$join = preg_replace_callback('!(ON )(\w+)\.(\w+)(\s+)!', function($m) { return $m[1].bors_class_field_to_db($m[2], $m[3]).$m[4];}, $join);
	$join = preg_replace_callback('!(=\s*|>|<)(\w+)\.(\w+)!', function($m) { return $m[1].bors_class_field_to_db($m[2], $m[3]);}, $join);
//	if(config('is_debug')) echo "    ??? result1: $join <br/>\n";
	$join = preg_replace_callback('!^(\w+)((\s+NOT)?\s+IN)!', function($m) use ($class_name) { return bors_class_field_to_db($class_name, $m[1]).$m[2];}, $join);
	$join = preg_replace_callback('!([ \(])(\w+)\s*(=|>|<)!', function($m) use ($class_name) { return $m[1].bors_class_field_to_db($class_name, $m[2]).$m[3];}, $join);

	if($class_name)
	{
		$join = preg_replace_callback('!^(\w+)(\s*(=|>|<))!', function($m) use ($class_name, $was_joined) { return bors_class_field_to_db($class_name, $m[1], (bool)$was_joined).$m[2];}, $join);
		$join = preg_replace_callback('!^(\w+)$!', function($m) use ($class_name, $was_joined, $for_order) { return bors_class_field_to_db($class_name, $m[1], (bool)$was_joined, (bool)$for_order);}, $join);
	}

	return $join;
}

function mysql_args_compile($args, $class=NULL)
{
//	if(config('is_debug')) echo "<b>mysql_args_compile</b>(".print_r($args, true).", $class) <br/>\n";

	if(!empty($args['*class_name']))
	{
		$class = $args['*class_name'];
		unset($args['*class_name']);
	}

	if(!empty($args['*set']))
	{
		$set = $args['*set'];
		unset($args['*set']);
	}
	else
		$set = '';

	$join = array();

	foreach(array('inner', 'left') as $join_type)
	{
		if($js = popval($args, "*{$join_type}_joins"))
			foreach($js as $j)
				$join[] = bors_upper($join_type).' JOIN '.$j;
	}

	if(!empty($args['inner_join']))
	{
		if(is_array($args['inner_join']))
		{
			foreach($args['inner_join'] as $j)
				$join[] = 'INNER JOIN '.mysql_bors_join_parse($j, $class);
		}
		else
			$join[] = 'INNER JOIN '.mysql_bors_join_parse($args['inner_join'], $class);

		unset($args['inner_join']);
	}

	if(!empty($args['left_join']))
	{
		if(is_array($args['left_join']))
		{
			foreach($args['left_join'] as $j)
				$join[] = 'LEFT JOIN '.mysql_bors_join_parse($j, $class);
		}
		else
			$join[] = 'LEFT JOIN '.mysql_bors_join_parse($args['left_join'], $class);

		unset($args['left_join']);
	}

	$join = join(' ', $join);

	$limit = mysql_limits_compile($args);
	unset($args['limit']);
	unset($args['page']);
	unset($args['per_page']);

	$order = popval($args, '*raw_order');
	if(!$order && !empty($args['order']))
	{
		$order = mysql_order_compile($args['order'], $class);

		unset($args['order']);
	}

	if(!empty($args['use_index']))
	{
		$use_index = "USE INDEX ({$args['use_index']})";

		unset($args['use_index']);
	}
	else
		$use_index = '';

	$raw_group = popval($args, '*raw_group');
	if(!$raw_group && !empty($args['group']))
	{
		$group = popval($args, 'group');
		if(preg_match('/^\*BY([A-Z]+)\(UNIX_TIMESTAMP\((`\w+`)\)\)$/', $group, $m))
		{
			switch($m[1])
			{
				case 'DAYS':
					$group = "YEAR({$m[2]}),MONTH({$m[2]}),DAY({$m[2]})";
					$args['*select'] = "DATE(FROM_UNIXTIME({$m[2]})) AS group_date";
					$args['*select_index_field*'] = 'group_date';
					break;
				case 'MONTHS':
					$group = "YEAR({$m[2]}),MONTH({$m[2]})";
					$args['*select'] = "CONCAT(YEAR(FROM_UNIXTIME({$m[2]})),'-',MONTH(FROM_UNIXTIME({$m[2]}))) AS group_date";
					$args['*select_index_field*'] = 'group_date';
					break;
			}
		}

		if($group)
		{
			$group = mysql_bors_join_parse($group);
			$raw_group = "GROUP BY {$group}";
		}
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

	return "{$use_index} {$join} {$set} {$where} {$raw_group} {$having} {$order} {$limit}";
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
