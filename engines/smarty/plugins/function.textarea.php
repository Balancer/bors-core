<?php
function smarty_function_textarea($params, &$smarty)
{
		extract($params);

		$obj = $smarty->get_template_vars('form');

		if(!isset($value))
			$value = $obj ? $obj->$name() : NULL;

		if(!isset($value))
			$value = session_var("form_value_{$name}");

		set_session_var("form_value_{$name}", NULL);

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

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			echo "<tr><th>{$th}</th><td>";
			if(empty($tyle))
				$style = "width: 99%";
		}

		echo "<textarea name=\"$name\"";
		foreach(explode(' ', 'class id style rows cols') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo ">".htmlspecialchars($value)."</textarea>\n";

		if($th)
			echo "</td></tr>\n";
}
