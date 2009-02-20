<?php

define(BORS_CORE, dirname(dirname(__FILE__)));
define(BORS_LOCAL, BORS_CORE.'/../.bors-local');
include_once("../config.php");

main($argv);

function main($argv)
{
	$class_name = $argv[1];

//	$class_file = secure_path(class_include($argv[1]));
//	$src = file_get_contents($class_file);

	$cls = new $class_name(NULL);
	foreach($cls->main_table_fields() as $property => $db_field)
	{
		if(is_numeric($property))
			$property = $db_field;
			
		if($property == 'id')
			continue;

		if(!method_exists($cls, $property))
			echo "function {$property}() { return \$this->stb_{$property}; }\n";
		if(!method_exists($cls, "set_{$property}"))
			echo "function set_{$property}(\$v, \$dbup) { return \$this->fset('{$property}', \$v, \$dbup); }\n";
	}
}
