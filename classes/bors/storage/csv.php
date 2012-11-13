<?php

class bors_storage_csv extends bors_storage
{
	var $data = array();

	// Загрузчик и парсер
	function init($obj)
	{
		static $is_loaded = false;
		if($is_loaded)
			return;

		if(!file_exists($obj->file_name()))
			return;

		$delimiter = $obj->get('delimiter', ',');

		$fh = fopen($obj->file_name(), 'rt');
		if($obj->first_line_header())
			$fields = fgetcsv($fh, 0, $delimiter);
		else
			$fields = $obj->fields();

		while($row = fgetcsv($fh, 0, $delimiter))
		{
			$x = array();
			foreach($fields as $id => $title)
				$x[$title] = $row[$id];
			$this->data[] = $x;
		}
		fclose($fh);

		$is_loaded = true;
	}

	function load_array($class_name_or_foo_object, $where)
	{
		if(!is_object($class_name_or_foo_object))
		{
			$obj = new $class_name(NULL);
			$obj->_configure();
		}
		else
		{
			$obj = $class_name_or_foo_object;
			$class_name = $obj->class_name();
		}

		$this->init($obj);

		$result = array();

		if(empty($this->data))
			return $result;

		foreach($this->data as $row)
		{
			$obj->data = $row;
			$result[] = $obj;

			$obj = new $class_name(NULL);
			$obj->_configure();
		}

		return $result;
	}
}
