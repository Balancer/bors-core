<?php

class bors_object_csv extends bors_object
{
	function storage_engine() { return 'bors_storage_csv'; }

	// Имя файла с данными.
	// Если не переопределено, то вычисляется из имени класса.
	function _file_name_def()
	{
		bors_throw('Ещё не реализовано. Указывайте CSV-файл явно.');
	}

	// Имена столбцов CVS
	function _fields_def()
	{
		bors_throw('Не заданы имена столбцов');
	}

}
