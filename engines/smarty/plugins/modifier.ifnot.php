<?php
function smarty_modifier_ifnot($cond, $value)
{
	return $cond ? $cond : $value;
}
