<?php

class bors_core_find
{
	var $_class_name;
	var $_where = array();

	function __construct($class_name)
	{
		$this->_class_name = $class_name;
	}

	function first()
	{
		return bors_find_first($this->_class_name, $this->_where);
	}

	// Найти все объекты, соответствующие заданным критериям
	function all($limit1=NULL, $limit2=NULL)
	{
		return bors_find_all($this->_class_name, $this->_where);
	}

	function count()
	{
		return bors_count($this->_class_name, $this->_where);
	}

	function where($conditions)
	{
		if(is_array($conditions))
			$this->_where = array_merge($this->_where, $conditions);
		else
			$this->_where[] = $conditions;
	}
}
