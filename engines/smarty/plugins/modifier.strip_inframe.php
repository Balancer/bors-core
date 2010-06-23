<?php
function smarty_modifier_strip_inframe($string)
{
	return preg_replace('/[\?\&]inframe=yes/', '', $string);
}
