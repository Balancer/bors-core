<?php
function smarty_modifier_get_array($array, $idx)
{
	if(!is_array($array))
		return "get <b>$idx</b> error: '{$array}' is not array";

	return $array[$idx];
}
