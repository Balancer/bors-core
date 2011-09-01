<?php

class driver_pdo_sqlite extends driver_pdo
{
	static function dsn($db_name)
	{
		if($dsn = configh('pdo_access', $db_name, 'dsn'))
			return $dsn;

		return 'sqlite:'.$db_name;
	}

	protected function _reconnect()
	{
		$dir = dirname($this->database);
		if(!file_exists($dir))
			mkpath($dir, 0750);

		debug_timing_start('pdo_sqlite_connect');

		$this->connection = new PDO(self::dsn($this->database));
		debug_timing_stop('pdo_sqlite_connect');
	}

	static function save_sql_function($type)
	{
		$map = array(
//			'timestamp' => "datetime(%d, 'unixepoch')",
		);

		return @$map[$type];
	}

	static function load_sql_function($type)
	{
		$map = array(
//			'timestamp' => "strftime('%%s', %s)",
		);

		return @$map[$type];
	}
}
