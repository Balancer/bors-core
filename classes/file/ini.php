<?php

/**
	Пример использования:
	$data = file_ini::load(BORS_SITE.'/vars.ini');
	var_dump($data);
*/

class file_ini
{
	private $data = array();

	function load($file_in)
	{
		$file = bors_data_meta::find_file($file_in);
		if(is_null($file))
			return NULL;

		$this->data = parse_ini_file($file, true);
		return true;
	}

	function save()
	{
		bors_use('fs/write_ini_file');
	}
}
