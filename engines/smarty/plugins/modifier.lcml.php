<?php
function smarty_modifier_lcml($string)
{
	require_once('engines/lcml/main.php');
    return lcml($string);
}
