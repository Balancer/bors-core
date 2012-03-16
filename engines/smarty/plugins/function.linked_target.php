<?php

function smarty_function_linked_target($params, &$smarty)
{
		extract($params);

		$object = bors_templates_smarty::get_var($smarty, 'form');
		if(!array_key_exists('value', $params))
		{
			// Автоматизация? target_class_name(target_id)?
			$value = $object->$name();
		}

		if(empty($value) && !$smarty->get_template_vars('no_session_vars'))
			$value = session_var("form_value_{$name}");

		set_session_var("form_value_{$name}", NULL);

		if(!isset($value) && isset($def))
			$value = $def;

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			echo "<tr><th>{$th}</th><td>";
			if(empty($style))
				$style = "width: 99%";
		}

		echo "<input type=\"text\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

		foreach(explode(' ', 'class id maxlength size style') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";

//		$id = uniqid();
//		echo "<div id=\"$id\"></div>";
//		template_jquery();
//		template_js('$(function() { $("#'.$id.'").click(function(){alert("!")}); })');

		if($th)
			echo "</td></tr>\n";

		$ts = base_object::template_data('form_linked_targets');
		$ts[] = $name;
		base_object::add_template_data('form_linked_targets', $ts);
}
