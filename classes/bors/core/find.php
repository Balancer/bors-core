<?php

class bors_core_find
{
	var $_class_name;
	var $_where = array();

	function __construct($class_name)
	{
		$this->_class_name = $class_name;
	}

	// Найти все объекты, соответствующие заданным критериям
	function all()
	{
		return bors_find_all($this->_class_name, $this->_where());
	}


}
