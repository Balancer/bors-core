<?php

require_once('../config.php');

config_seth('dbal', 'DBAL_TEST', 'driver', 'pdo_sqlite');
config_seth('dbal', 'DBAL_TEST', 'path', dirname(__FILE__).'/dbal-test.sqlite');

config_seth('dbal', 'DBAL_TEST_MYSQL', 'driver', 'pdo_mysql');
config_seth('dbal', 'DBAL_TEST_MYSQL', 'dbname', 'BORS-UNITTEST');
config_seth('dbal', 'DBAL_TEST_MYSQL', 'user', 'tester');
config_seth('dbal', 'DBAL_TEST_MYSQL', 'password', 'UERZhxQGCXZJTjzD');

class dbal_test extends base_object_db
{
	function storage_engine() { return 'bors_storage_dbal'; }

	function db_name() { return 'DBAL_TEST'; }
	function table_name() { return 'test_table'; }
	function table_fields()
	{
		return array(
			'id' => 'test_id',
			'title',
		);
	}
}

$storage_class = dbal_test::storage_engine();
$storage = new $storage_class(NULL);
$storage->create_table('dbal_test');

$x = object_new_instance('dbal_test', array(
	'title' => 'test '.time(),
));

echo $x."\n";
