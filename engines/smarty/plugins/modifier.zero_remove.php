<?php
function smarty_modifier_zero_remove($string)
{
	if($string == 0)
		return '';

	return $string;
}
