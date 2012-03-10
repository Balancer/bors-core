<?php

function smarty_function_password($params, &$smarty)
{
	extract($params);


	if(method_exists($smarty, 'getTemplateVars'))
	{
		$obj = $smarty->getTemplateVars('form');
		$ajax_validation = $smarty->getTemplateVars('ajax_validate');
	}
	else
	{
		$obj = $smarty->get_template_vars('form');
		$ajax_validation = $smarty->get_template_vars('ajax_validate');
	}

	$value = $obj ? $obj->$name() : '';

	$class = empty($class) ? array() : explode(' ', $class);

	if(in_array($name, explode(',', session_var('error_fields'))))
		$class[] = "error";

	// Если у нас используется валидация данных формы
	if($ajax_validation)
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

	foreach(explode(' ', 'class id maxlength size style') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";

	echo " />\n";
}
