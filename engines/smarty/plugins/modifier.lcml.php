<?php
function smarty_modifier_lcml($string)
{
	require_once BORS_CORE.'/engines/lcml/main.php';
    return lcml($string);
}
