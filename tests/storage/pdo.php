<?php

require_once('../config.php');

config_seth('pdo_access', 'PDO_TEST', 'dsn', 'sqlite:'.dirname(__FILE__).'/test.sqlite');

class pdo_test extends base_object_db
{
	function storage_engine() { return 'bors_storage_pdo'; }

	function db_name() { return 'PDO_TEST'; }
	function table_name() { return 'test_table'; }
	function table_fields()
	{
		return array(
			'id' => 'test_id',
			'title',
		);
	}
}

$storage_class = pdo_test::storage_engine();
$storage_class::create_table('pdo_test');

$x = object_new_instance('pdo_test', array(
	'title' => 'test '.time(),
));

echo $x."\n";