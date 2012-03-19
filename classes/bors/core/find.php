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
		return bors_find_first($this->_class_name, $this->_where);
	}

	// Найти все объекты, соответствующие заданным критериям
	function all($limit1=NULL, $limit2=NULL)
	{
		$init = new $this->_class_name(NULL);
		$class_file = bors_class_loader::load($this->_class_name);
		$init->set_class_file($class_file);

		$s = $init->storage();

		$this->_where['*by_id'] = true;

		$objects = $s->load_array($init, $this->_where);

		if(config('debug_objects_create_counting_details'))
		{
			debug_count_inc($class.': bors_find_all_calls');
			debug_count_inc($class.': bors_find_all_total ', count($objects));
		}

		if($this->_preload)
		{
			foreach($preload as $x)
				if(preg_match('/^(\w+)\((\w+)\)$/', $x, $m))
					bors_objects_preload($objects, $m[2], $m[1]);
		}

		return $objects;
	}

	function count()
	{
		return bors_count($this->_class_name, $this->_where);
	}

	function where($param, $value = NULL)
	{
		if(!is_null($value))
			$this->where_parse_set($param, $value);
		elseif(is_array($conditions))
			$this->_where = array_merge($this->_where, $conditions);
		else
			$this->_where[] = $conditions;

		return $this;
	}

	function inner_join($join_class, $join_cond)
	{
		$this->class_stack_push($join_class);
		$table = bors_lib_orm::table_name($join_class);
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
			$this->_where[$name] = array_merge($this->_where, array($value));
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

	function where_parse_set($param, $value)
	{
		$param = $this->stack_parse($param);
		if(preg_match('/ IN$/', $param))
		{
			if(is_array($value))
				$param = "$param ('".join("','", array_map('addslashes', $value))."')";
			else
				bors_throw("Parse where conditions error: where('$param', '$value')");
		}

		$this->_add_where_array('*raw_conditions', $param);
	}

	function order($order)
	{
		$parsed_order = array();
		foreach(explode(',', $order) as $property_name)
		{
			$property_name = trim($property_name);
			if(preg_match('/^-(.+)/', $property_name, $m))
			{
				$property_name = $m[1];
				$dir = 'DESC';
			}
			else
				$dir = 'ASC';

			$field_data = bors_lib_orm::parse_property($this->_class_name, $property_name);

			$parsed_order[] = "{$field_data['name']} $dir";
		}

		$this->_where['*raw_order'] = "ORDER BY ".join(', ', $parsed_order);

		return $this;
	}
}
