<?php

config_set('phpunit_include', 'PHPUnit');
config_set('output_charset', 'utf8');

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__).'/htdocs';

register_vhost('localhost', $_SERVER['DOCUMENT_ROOT']);

config_set('unit-test.mysql.db', 'BORS_UNIT_TEST');
config_set('can-drop-tables', true);

function bors_unit_test_up()
{
	$dbh = new driver_mysql(config('unit-test.mysql.db'));
}

require_once('config-host.php');