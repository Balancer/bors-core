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

		$class = empty($class) ? array() : explode(' ', $class);
		if(in_array($name, explode(',', session_var('error_fields'))))
			$class[] = "error";

		$versioning = object_property($obj, 'versioning_properties', array());
		if(array_key_exists($name, $versioning))
		{
			$has_versioning = true;
			$previous = $versioning[$name];
			$class[] = 'yellow_box';
		}
		else
			$has_versioning = false;

		$class = join(' ', $class);

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			echo "<tr><th>{$th}</th><td>";
			if(empty($style))
				$style = "width: 99%";
		}

		if(@$type == 'bbcode')
		{
			static $tmp_id = 0;
			if(!$id)
				$id = 'tmp_id_'.($tmp_id++);
			template_jquery_markitup('#'.$id);
		}

		echo "<textarea name=\"$name\"";
		foreach(explode(' ', 'class id style rows cols') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo ">".htmlspecialchars($value)."</textarea>\n";

		if($has_versioning)
			echo "<br/><small>".ec("Исходное значение: ").$previous."</small>\n";

		if($th)
			echo "</td></tr>\n";
}
