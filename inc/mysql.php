<?php

function mysql_where_compile($conditions_array, $class='', $was_joined = true)
{
	if(isset($conditions_array['where']))
		$conditions_array = $conditions_array['where'];

	if(empty($conditions_array))
		return '';

	$where = array();
	foreach($conditions_array as $field_cond => $value)
	{

		$value = str_replace('%ID%', '%MySqlStorageOID%', $value);

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

function bors_class_field_to_db($class, $property = NULL, $was_joined = true)
{
	if(!$class)
		return $property;

	if(!is_object($class))
	{
		if(!class_exists($class))
			return $property ? $class.'.'.$property : $class;

		$class = new $class(NULL);
	}

	$found = false;
	foreach($class->fields_map_db() as $db => $tables)
	{
		foreach($tables as $table => $fields)
		{
			if(preg_match('!^(\w+)\((.+)\)$!', $table, $m))
				$table = $m[1];

			if(($field = @$fields[$property]))
			{
				$found = true;
				break;
			}

			$fields	 = array_smart_expand($fields);
			if(($field = @$fields[$property]))
			{
				$found = true;
				break;
			}
		}

		if($found)
			break;
	}

	if(!$field)
		return $property;

	if(is_array($field))
	{
		bors_lib_orm::field($property, $field);
		$field = $field['name'];
	}

	if(preg_match('!^(.+)\|.+!', $field, $m))
		$field = $m[1];

	if(!$was_joined && $table == $class->table_name())
		$table = '';

	if(preg_match('/^(\w+)\((\w+)\)$/', $field, $m))
		return $m[1].'('.($table ? $table.'.' : '') . $m[2] .')';
	else
		return (@$table ? $table.'.' : '') . $field;
}

function mysql_bors_join_parse($join, $class_name='', $was_joined = true)
{
	$join = preg_replace('!(\w+)\s+ON\s+!e', 'bors_class_field_to_db("$1")." ON "', $join);
	$join = preg_replace('!^(\w+)\.(\w+)$!e', 'bors_class_field_to_db("$1", "$2")."$3"', $join);
	$join = preg_replace('!(\w+)\.(\w+)\s*(=|>|<)!e', 'bors_class_field_to_db("$1", "$2")."$3"', $join);
	$join = preg_replace('!(\w+)\.(\w+)(\s+IN)!e', 'bors_class_field_to_db("$1", "$2")."$3"', $join);
//	$join = preg_replace('!(ON )(\w+)\.(\w+)(\s+)!e', '"$1".bors_class_field_to_db("$2", "$3")."$4"', $join);
	$join = preg_replace('!(=\s*|>|<)(\w+)\.(\w+)!e', '"$1".bors_class_field_to_db("$2", "$3")', $join);
	$join = preg_replace('!^(\w+)((\s+NOT)?\s+IN)!e', 'bors_class_field_to_db("$class_name","$1")."$2"', $join);
	$join = preg_replace('!(\w+)\s*(=|>|<)!e', 'bors_class_field_to_db("$class_name", "$1")."$2"', $join);

	if($class_name)
	{
		$join = preg_replace('!^(\w+)(\s*(=|>|<))!e', 'bors_class_field_to_db("'.$class_name.'", "$1", '.($was_joined ? 1 : 0).')."$2"', $join);
		$join = preg_replace('!^(\w+)$!e', 'bors_class_field_to_db("'.$class_name.'", "$1", '.($was_joined ? 1 : 0).')."$2"', $join);
	}

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

	return "{$use_index} {$join} {$where} {$group} {$having} {$order} {$limit}";
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
