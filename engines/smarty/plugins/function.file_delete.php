<?php

function smarty_function_file_delete($params, &$smarty)
{
	extract($params);

	$obj = $smarty->get_template_vars('form');

	if(!$obj->$name())
		return;

	echo "<input type=\"checkbox\" name=\"file_{$name}_delete_do\" />&nbsp;$value";
}
