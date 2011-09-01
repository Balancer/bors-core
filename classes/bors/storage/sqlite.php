<?php

// Не мудрствуя лукаво делаем sqlite-бэкенд на PDO.

class bors_storage_sqlite extends bors_storage_pdo
{
	// Преобразуем имя БД, каким её возвращает db_name() объекта
	// в понимаемый PDO DSN.

//	function _pdo_dsn($db_name) { return 'sqlite:'.$db_name; }

	function _db_driver_name() { return 'driver_pdo_sqlite'; }

	function _fields_types()
	{
		return array(
			'string'	=>	'VARCHAR(255)',
			'text'		=>	'TEXT',
			'bbcode'	=>	'TEXT',
			'timestamp'	=>	'INT',
			'int'		=>	'INTEGER',
			'uint'		=>	'INTEGER UNSIGNED',
			'bool'		=>	'TINYINT(1) UNSIGNED',
			'float'		=>	'NUMERIC',
			'enum'		=>	'ENUM(%)',

//			'*autoinc'	=>	'',
//			'*primary_in_field'	=> ' PRIMARY KEY',
			'*primary_post_field'	=> '',
			'*id_field_declaration'	=>	'%s INTEGER PRIMARY KEY AUTOINCREMENT',
		);
	}
}
