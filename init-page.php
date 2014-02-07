<?php

require_once __DIR__.'/init.php';

function bors_show($class_name, $id=NULL)
{
	$obj = bors_load($class_name, $id);
	echo $obj->content();
}
