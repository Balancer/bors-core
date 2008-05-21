<?php
function smarty_modifier_get($object, $field, $param = false)
{
	if(!$object)
		return "get <b>$field</b> for NULL object";

	if(!is_object($object))
		return "get <b>$field</b> error: '{$object}' is not object";

	return $param === false ? $object->$field() : $object->$field($param);
}
