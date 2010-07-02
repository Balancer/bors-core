<?php

function smarty_function_input($params, &$smarty)
{
		extract($params);

		if(!isset($value))
		{
			$obj = $smarty->get_template_vars('current_form_class');
			if(($obj && $obj->id()))
				$value = preg_match('!^\w+$!', $name) ? (isset($value)?$value : ($obj?$obj->$name():NULL)) : '';
			else
				$value = NULL;
		}

		if(!isset($value))
			$value = session_var("form_value_{$name}");

//		echo "===$value===".session_var("form_value_{$name}");

		set_session_var("form_value_{$name}", NULL);

		if(!isset($value) && isset($def))
			$value = $def;

		if(empty($maxlength))
			$maxlength = 255;

		if(!empty($do_not_show_zero) && $value == 0)
			$value = '';

		$class = empty($class) ? array() : explode(' ', $class);

		if(in_array($name, explode(',', session_var('error_fields'))))
			$class[] = "error";

		// Если у нас используется валидация данных формы
		if($smarty->get_template_vars('ajax_validate'))
		{
			if(empty($id))
			{
				static $input_id = 0;
				$id = 'input_id_'.($input_id++);
			}

			if(!empty($ajax_validator))
				$class[] = 'validate['.$ajax_validator.']';
		}

//		class="validate[required,custom[noSpecialCaracters],length[5,20]]"

		$class = join(' ', $class);

		echo "<input type=\"text\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

		foreach(explode(' ', 'class id maxlength size style') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";
}
