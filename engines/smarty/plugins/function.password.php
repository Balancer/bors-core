<?php

function smarty_function_password($params, &$smarty)
{
	extract($params);

	$obj = $smarty->get_template_vars('current_form_class');
	$value = $obj ? $obj->$name() : '';

	$class = empty($class) ? array() : explode(' ', $class);

	if(in_array($name, explode(',', session_var('error_fields'))))
		$class[] = "error";

	// Если у нас используется валидация данных формы
	if($smarty->get_template_vars('ajax_validate'))
	{
		if(empty($id))
		{
			static $input_id = 0;
			$id = 'password_id_'.($input_id++);
		}

		if(!empty($ajax_validator))
			$class[] = 'validate['.$ajax_validator.']';
	}

	$class = join(' ', $class);

	echo "<input type=\"password\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

	foreach(split(' ', 'class id maxlength size style') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";

	echo " />\n";
}
