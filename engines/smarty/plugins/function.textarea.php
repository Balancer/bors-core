<?php
function smarty_function_textarea($params, &$smarty)
{
		extract($params);

		$obj = $smarty->get_template_vars('current_form_class');

		if(!isset($value))
			$value = $obj ? $obj->$name() : NULL;

		if(empty($rows))
			$rows = 7;

		if(empty($cols))
			$cols = 50;

		if(in_array($name, explode(',', session_var('error_fields'))))
		{
			if(empty($class))
				$class = "error";
			else
				$class .= " error";
		}

		echo "<textarea name=\"$name\"";
		foreach(split(' ', 'class id style rows cols') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo ">".htmlspecialchars($value)."</textarea>\n";
}
