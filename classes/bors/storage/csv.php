<?php

class bors_storage_csv extends bors_storage
{
	var $data;

	// Загрузчик и парсер
	function init($obj)
	{
		static $is_loaded = false;
		if($is_loaded)
			return;

		$fh = fopen($obj->file_name(), 'rt');
		$fields = $obj->field_names();
		while($row = fgetcsv($fh))
		{
			$x = array();
			foreach($fields as $id => $title)
				$x[$title] = $row[$id];
			$this->data[] = $x;
		}

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
			$obj = $class_name_or_foo_object;

		$this->init($obj);

		$result = array();

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
