<?php

class bors_storage extends base_null
{
	static private $__back_functions = array(
		'html_entity_decode' => 'htmlspecialchars',
		'UNIX_TIMESTAMP' => 'FROM_UNIXTIME',
		'aviaport_old_denormalize' => 'aviaport_old_normalize',
		'stripslashes' => 'addslashes',
	);

	static function post_functions_do($object, $functions)
	{
		foreach($functions as $property => $function)
			$object->{"set_$property"}($function($object->$property()), false);
	}

	function save($object)
	{
		echo '???';
		echo debug_trace();
		throw new Exception('store() method not implemented yet in '.$this->class_name());
	}
}
