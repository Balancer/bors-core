<?php

require_once('../../../../tools/config.php');
include_once(BORS_CORE.'/init.php');

main(@$argv[1]);

function main($sqlt_file)
{
	if(!$sqlt_file)
		exit("Укажите файл шаблона!\n");

	if(!file_exists($sqlt_file))
		exit("Файл $sqlt_file не найден!\n");

	$sqlt = bors_load('bors_tools_codegen_sqlt', NULL);
	$sqlt->sqlt_parse(file_get_contents($sqlt_file));

	$base = preg_replace('/\.sqlt$/', '', $sqlt_file);
	file_put_contents($sqlt->table_name().".sql", $sqlt->make_mysql_create());
	file_put_contents($sqlt->class_name().".php", $sqlt->make_class());
}
