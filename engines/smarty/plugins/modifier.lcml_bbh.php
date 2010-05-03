<?php
function smarty_modifier_lcml_bbh($string, $nocache = false)
{
	require_once('engines/lcml/main.php');

	return lcml_bbh($string);
}
