<?php

include_once('../config.php');
include_once(BORS_CORE.'/config.php');

main($argv);

function main($argv)
{
	if(empty($argv[2]))
		exit("Use {$argv[0]} table [db] [host]\n");

	$table = $argv[2];
	$db    = empty($argv[3]) ? config('main_bors_db') : $argv[3];
//	$host  = empty($argv[4]) ? 'localhost' : $argv[4];

	$dbh = new driver_mysql($db);

	$x = $dbh->get("SHOW CREATE TABLE $table");

	echo "<?php\n\n";
	echo "class {$table} extends base_object_db\n{\n";
	echo "\tfunction main_table() { return '$table'; }\n";
	echo "\tfunction main_table_fields()\n\t{\n\t\treturn array(\n";
	foreach(explode("\n", $x['Create Table']) as $s)
	{
		if(preg_match('/^\s+`(\w+)`/', $s, $m))
			echo "\t\t\t'{$m[1]}',\n";
	}
	echo "\t\t);\n";
	echo "\t}\n";
	echo "}\n";
}
