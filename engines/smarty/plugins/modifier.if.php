<?php
function smarty_modifier_if($cond, $value)
{
	if($cond)
		return strpos($value, '%') !== false ? str_replace('%', $cond, $value) : $cond;

	return '';
}
