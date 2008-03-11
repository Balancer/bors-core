<?php
function smarty_modifier_lcml($string)
{
	require_once('engines/lcml.php');
    return lcml($string);
}
