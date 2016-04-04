<?php
function smarty_modifier_get_array($array, $idx)
{
	if(!is_array($array))
	{
		bors_debug::syslog('__data_error', "get $idx error: '{$array}' is not array");
		return '';
	}

	return @$array[$idx];
}
