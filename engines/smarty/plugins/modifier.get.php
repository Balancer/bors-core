<?php
function smarty_modifier_get($object, $field, $param1 = false, $param2 = false)
{
	if(!$object)
	{
		debug_hidden_log('__data_error', "get $field for NULL object");
		return '';
	}

	if(!is_object($object))
	{
		debug_hidden_log('__data_error', "get $field error: '{$object}' is not object");
		return '';
	}

	$params = array();
	if($param1 !== false)
		$params[] = $param1;
	if($param2 !== false)
		$params[] = $param2;

	return $params ? call_user_func_array(array(&$object, $field), $params) : object_property($object, $field);
}
