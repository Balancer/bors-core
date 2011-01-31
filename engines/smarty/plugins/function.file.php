<?php

function smarty_function_file($params, &$smarty)
{
	extract($params);

	$obj = $smarty->get_template_vars('form');

	echo "<input type=\"file\" name=\"$name\"";

	foreach(explode(' ', 'class style') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";

	echo " />\n";

	$vars = base_object::template_data('form_file_vars');
	if(!empty($id_field))
		$vars[] = "$name=".(empty($class_name_field) ? '' : $class_name_field)."($id_field)";
	else
		$vars[] = $name;
	base_object::add_template_data('form_file_vars', $vars);
}
