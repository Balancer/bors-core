<?php

class b2_kv_sqlite extends b2_kv_meta
{
	private $db = NULL;

	static function instance()
	{
		static $instance = NULL;
		if($instance)
			return $instance;

		$kv = new b2_kv_sqlite;
		$db_file = config('host.kv_sqlite_file', BORS_HOST.'/data/bors.sqlite');
		$kv->db = sqlite_open($db_file, 0666);
		sqlite_query($kv->db, 'CREATE TABLE IF NOT EXISTS key_value(section CHAR(255), name CHAR(255), value TEXT);');

		b2:on_shutdown(array('b2_kv_sqlite', 'on_shutdown'));

		return $instance = $kv;
	}

	static function on_shutdown()
	{
		$kv = b2_kv_sqlite::instance();
		sqlite_close($kv->db);
	}

	function set($section, $name, $value)
	{
		$kv = b2_kv_sqlite::instance();
	}

//	function get($section, $name)
}
