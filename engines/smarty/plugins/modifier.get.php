<?php
function smarty_modifier_get($object, $field, $param1 = false, $param2 = false)
{
	if(!$object)
		return "get <b>$field</b> for NULL object";

	if(!is_object($object))
		return "get <b>$field</b> error: '{$object}' is not object";

	$params = array();
	if($param1 !== false)
		$params[] = $param1;
	if($param2 !== false)
		$params[] = $param2;

	return $params ? call_user_func_array(array(&$object, $field), $params) : $object->$field();
}
