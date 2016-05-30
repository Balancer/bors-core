<?php
function smarty_modifier_lcml_bbh($string, $nocache = false)
{
	require_once BORS_CORE.'/engines/lcml/main.php';

	return lcml_bbh($string);
}
