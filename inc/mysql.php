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
			$where[] = mysql_bors_join_parse($w, $class, $was_joined);
	}

	return 'WHERE '.join(' AND ', $where);
}

function mysql_order_compile($order_list, $class_name = false)
{
	if(empty($order_list))
		return '';

	$order_list = mysql_bors_join_parse($order_list, $class_name);

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

function mysql_limits_compile(&$args)
{
	if($limit = intval(popval($args, '*limit')))
		return "LIMIT $limit";

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

function bors_class_field_to_db($class, $property = NULL, $was_joined = true)
{
//	echo "<i>bors_class_field_to_db</i>($class, $property, $was_joined) <br/>\n";
	if(!$class)
		return $property;

	if(!is_object($class))
	{
		if(!class_exists($class))
			return $property ? $class.'.'.$property : $class;

		$class = new $class(NULL);
	}

//	if(config('is_debug') && $class) var_dump(bors_lib_orm::all_fields($class));

	if($field = bors_lib_orm::property_to_field($class, $property))
	{
		if($was_joined && preg_match('/^\w+$/', $field)) // Если это JOIN и имя поля буквеннон, то допишем имя таблицы
		{
//			if(config('is_debug')) echo "tn={$class->table_name()}, mt={$class->main_table()} -> {$class->table_name()}.{$field}<br/>\n";
			return $class->table_name().'.'.$field;
		}
		else // Иначе имя таблицы уже дописано.
			return $field;
	}

	if(!$property)
		return $class->table_name();

	return $property;
}

function mysql_bors_join_parse($join, $class_name='', $was_joined = true)
{
/*	if(config('is_debug'))
	{
		echo "mysql_bors_join_parse($join, $class_name, $was_joined) <br/>\n";
		if(preg_match('/posts.posts/', $was_joined))
			echo debug_trace();
	}
*/
//	if(config('is_developer')) echo "--- $join <br/>\n";

	$join = preg_replace('!(\w+)\s+ON\s+!e', 'bors_class_field_to_db("$1")." ON "', $join);
	$join = preg_replace('!^(\w+)\.(\w+)$!e', 'bors_class_field_to_db("$1", "$2")."$3"', $join);
	$join = preg_replace('!(\w+)\.(\w+)\s*(=|>|<)!e', 'bors_class_field_to_db("$1", "$2")."$3"', $join);
	$join = preg_replace('!(\w+)\.(\w+)(\s+IN)!e', 'bors_class_field_to_db("$1", "$2")."$3"', $join);
	$join = preg_replace('!(\w+)\.(\w+)(\s+BETWEEN\s+\S+\s+AND\s+\S+)!e', 'bors_class_field_to_db("$1", "$2")."$3"', $join);
//	$join = preg_replace('!(ON )(\w+)\.(\w+)(\s+)!e', '"$1".bors_class_field_to_db("$2", "$3")."$4"', $join);
	$join = preg_replace('!(=\s*|>|<)(\w+)\.(\w+)!e', '"$1".bors_class_field_to_db("$2", "$3")', $join);
	$join = preg_replace('!^(\w+)((\s+NOT)?\s+IN)!e', 'bors_class_field_to_db("$class_name","$1")."$2"', $join);
//	if(config('is_debug')) echo "    ??? result1: $join <br/>\n";
	$join = preg_replace('!([ \(])(\w+)\s*(=|>|<)!e', '"$1".bors_class_field_to_db("$class_name", "$2")."$3"', $join);

//	if(config('is_developer')) echo "    --- result2: $join <br/>\n";

	if($class_name)
	{
		$join = preg_replace('!^(\w+)(\s*(=|>|<))!e', 'bors_class_field_to_db("'.$class_name.'", "$1", '.($was_joined ? 1 : 0).')."$2"', $join);
		$join = preg_replace('!^(\w+)$!e', 'bors_class_field_to_db("'.$class_name.'", "$1", '.($was_joined ? 1 : 0).')."$2"', $join);
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

	$group = popval($args, '*raw_group');
	if(!$group && !empty($args['group']))
	{
		if(preg_match('/^\*BY([A-Z]+)\(UNIX_TIMESTAMP\((`\w+`)\)\)$/', $args['group'], $m))
		{
			switch($m[1])
			{
				case 'DAYS':
					$args['group'] = "YEAR({$m[2]}),MONTH({$m[2]}),DAY({$m[2]})";
					$args['*select'] = "DATE(FROM_UNIXTIME({$m[2]})) AS group_date";
					$args['*select_index_field*'] = 'group_date';
					break;
				case 'MONTHS':
					$args['group'] = "YEAR({$m[2]}),MONTH({$m[2]})";
					$args['*select'] = "CONCAT(YEAR(FROM_UNIXTIME({$m[2]})),'-',MONTH(FROM_UNIXTIME({$m[2]}))) AS group_date";
					$args['*select_index_field*'] = 'group_date';
					break;
			}
		}

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

	return "{$use_index} {$join} {$set} {$where} {$group} {$having} {$order} {$limit}";
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
