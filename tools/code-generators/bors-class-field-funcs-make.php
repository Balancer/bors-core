<?php

ini_set('display_errors', 1);

include_once(realpath(dirname(__FILE__).'/../config-local.php'));
include_once(BORS_CORE.'/config.php');

main($argv);

function main($argv)
{
	$class_name = $argv[1];

//	$class_file = secure_path(class_include($argv[1]));
//	$src = file_get_contents($class_file);

	$cls = new $class_name(NULL);
	foreach($cls->fields_map() as $property => $db_field)
	{
		if(is_numeric($property))
			$property = $db_field;

		if($property == 'id')
			continue;

		if(!method_exists($cls, $property))
			echo "function {$property}() { return @\$this->data['{$property}']; }\n";
		if(!method_exists($cls, "set_{$property}"))
			echo "function set_{$property}(\$v, \$dbup) { return \$this->set('{$property}', \$v, \$dbup); }\n";
	}
}
