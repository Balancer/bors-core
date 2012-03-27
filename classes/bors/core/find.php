<?php

class bors_core_find
{
	var $_class_name;
	var $_where = array();
	var $_class_stack = array();
	var $_foo_storage;
	var $_preload;

	function __construct($class_name)
	{
		if(is_numeric($class_name))
			$class_name = class_id_to_name($class_name);

		$this->_class_name = $class_name;
		$this->_class_stack = array($class_name);
		$foo_object = new $class_name(NULL);
		$this->_foo_storage = $foo_object->storage();
	}

	function first()
	{
		return array_pop($this->all(1));
	}

	// Найти все объекты, соответствующие заданным критериям
	function all($limit1=NULL, $limit2=NULL)
	{
		debug_timing_start('bors_find::all()');

		$args = func_get_args();
		if(count($args) == 1)
			// Формат all($limit)
			$this->_where['*limit'] = $limit1;
		elseif(count($args) == 2)
			// Формат all($page, $items_per_page)
			$this->_where['*limit'] = array(($limit1-1)*$limit2, $limit2);

		$init = new $this->_class_name(NULL);
		$class_file = bors_class_loader::load($this->_class_name);
		$init->set_class_file($class_file);

		$s = $init->storage();

		$this->_where['*by_id'] = true;

		$objects = $s->load_array($init, $this->_where);

		config_set('debug.trace_queries', NULL);

		debug_timing_stop('bors_find::all()');

		if(config('debug_objects_create_counting_details'))
		{
			debug_count_inc($this->_class_name.': bors_find::all()_calls');
			debug_count_inc($this->_class_name.': bors_find::all()_total', count($objects));
		}

		if($this->_preload)
		{
			debug_timing_start('bors_find::all()_preload');
			foreach($preload as $x)
				if(preg_match('/^(\w+)\((\w+)\)$/', $x, $m))
					bors_objects_preload($objects, $m[2], $m[1]);
			debug_timing_stop('bors_find::all()_preload');
		}

		return $objects;
	}

	function count()
	{
		//TODO: сделать игнор в sql-драйвере
		unset($this->_where['*by_id']);
		unset($this->_where['*limit']);
		return bors_count($this->_class_name, $this->_where);
	}

	function where($param, $value = NULL, $value2 = NULL)
	{
		switch(count(func_get_args()))
		{
			case 2:
				$this->where_parse_set($param, $value);
				return $this;
			case 3:
				$this->where_parse_set($param, $value, $value2);
				return $this;
		}

		if(is_array($param))
			$this->_where = array_merge($this->_where, $param);
		else
			$this->_where[] = $param;

		return $this;
	}

	function inner_join($join_class, $join_cond)
	{
		$this->class_stack_push($join_class);

		if(preg_match('/^\w+$/', $join_class))
			$table = bors_lib_orm::table_name($join_class);
		else
			$table = $join_class;

		$join_cond = $this->stack_parse($join_cond);
		$this->_add_where_array('*inner_joins', "`$table` ON ($join_cond)");
		return $this;
	}

	function limit($limit)
	{
		$this->_where['*limit'] = $limit;
		return $this;
	}

	private function _add_where_array($name, $value)
	{
		if(empty($this->_where[$name]))
			$this->_where[$name] = array($value);
		else
			$this->_where[$name] = array_merge($this->_where[$name], array($value));
	}

	function class_stack_push($class_name)
	{
		if(is_numeric($class_name))
			$class_name = class_id_to_name($class_name);

		$this->_class_stack[] = $class_name;
	}

	function stack_parse($s)
	{
		return preg_replace_callback('/\*(\d+)\.(\w+)/', array($this, '_stack_parse_callback'), $s);
	}

	private function _stack_parse_callback($m)
	{
		$class_name = $this->_class_stack[$m[1]-1];
		$table = bors_lib_orm::table_name($class_name);
		$field_data = bors_lib_orm::parse_property($class_name, $m[2]);
		$field_name = $field_data['name'];
		if(!$field_name)
			bors_throw("Not defined table field for property '{$m[2]}' in class '{$class_name}' as '*{$m[1]}'");

		return "`$table`.`{$field_name}`";
	}

	function where_parse_set($param, $value, $value2 = NULL)
	{
		$param = $this->stack_parse($param);

		if(preg_match('/ IN$/', $param))
		{
			if(is_array($value))
				$param = "$param ('".join("','", array_map('addslashes', $value))."')";
			else
				bors_throw("Parse where conditions error: where('$param', '$value')");
		}
		elseif(preg_match('/ BETWEEN$/', $param))
		{
			if(is_array($value))
				$param = "$param '".join("','", array_map('addslashes', $value))."'";
			else
				$param = "$param '".addslashes($value)."' AND '".addslashes($value2)."'";
		}
		elseif(count(func_get_args()) == 2)
		{
			if(preg_match('/[\w`]$/', $param))
				$param .= " = ";

			$param .= "'".addslashes($value)."'";
		}

		$this->_add_where_array('*raw_conditions', $param);
	}

	//TODO: убрать преобразование, типа UNIXTIME(`Date`) as create_time … ORDER BY create_time там, где можно без него обойтись
	function order($order)
	{
		$parsed_order = array();
		foreach(explode(',', $order) as $property_name)
		{
			$property_name = trim($property_name);
			if(preg_match('/^-(.+)/', $property_name, $m))
			{
				$property_name = $m[1];
				$dir = ' DESC';
			}
			else
				$dir = '';

			if(preg_match('/^\w+$/', $property_name))
			{
				$field_data = bors_lib_orm::parse_property($this->_class_name, $property_name);
				$field_name = $field_data['name'];
			}
			else
				$field_name = $property_name;

			$parsed_order[] = "{$field_name}$dir";
		}

		$this->_where['*raw_order'] = "ORDER BY ".join(', ', $parsed_order);

		return $this;
	}

	function debug($type = 'hidden')
	{
//		$this->_where['*debug'] = $type;
		config_set('debug.trace_queries', $type);
		return $this;
	}

	function group($property_name)
	{
		if(preg_match('/^\w+$/', $property_name))
		{
			$field_data = bors_lib_orm::parse_property($this->_class_name, $property_name);
			$field_name = $field_data['name'];
		}
		else
			$field_name = $property_name;

		$this->_where['*raw_group'] = "GROUP BY `".addslashes($field_name)."`";

		return $this;
	}
}
