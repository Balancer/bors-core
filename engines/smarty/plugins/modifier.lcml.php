<?php
function smarty_modifier_lcml($string)
{
	require_once('funcs/lcml.php');
    return lcml($string);
}
