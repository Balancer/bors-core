<?php
function smarty_modifier_get($object, $field, $param = false)
{
	if(!$object)
		return "get <b>$field</b> for NULL object";

	return $param === false ? $object->$field() : $object->$field($param);
}
