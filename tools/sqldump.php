<?php

define('BORS_CORE', '/var/www/bors/bors-core');
define('BORS_LOCAL', '/var/www/bors/bors-airbase');
	
require_once(BORS_CORE.'/config.php');

$table = 'bors_class_names';

$dbh = new DataBase('BORS');

$dbh->query("SELECT * FROM $table");

$dbh->multi_insert_init($table);
while($row = $dbh->fetch())
	$dbh->multi_insert_add($table, $row);
	
echo "INSERT INTO $table ".join(",", $dbh->insert_buffer[$table]).";\n";
