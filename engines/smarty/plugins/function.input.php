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

		set_session_var("form_value_{$name}", NULL);

		if(!isset($value) && isset($def))
			$value = $def;

		if(empty($maxlength))
			$maxlength = 255;

		if(!empty($do_not_show_zero) && $value == 0)
			$value = '';

		if(in_array($name, explode(',', session_var('error_fields'))))
		{
			if(empty($class))
				$class = "error";
			else
				$class .= " error";
		}

		echo "<input type=\"text\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

		foreach(explode(' ', 'class style maxlength size') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";
}
