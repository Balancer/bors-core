<?php

class bors_forms_input extends bors_forms_element
{
	static function html($params, &$form)
	{
		$object = $form->object();

		$value = self::value($params, $form);

		$maxlength = defval($params, 'maxlength', 255);

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
		$versioning = object_property($object, 'versioning_properties', array());

		if(array_key_exists($name, $versioning))
		{
			$has_versioning = true;
			$previous = $versioning[$name];
			$class[] = 'yellow_box';
		}
		else
			$has_versioning = false;

		$class = join(' ', $class);

		$result = "";

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			$result .= "<tr><th>{$th}</th><td>";
			if(empty($style))
				$style = "width: 99%";
		}

		$result .= "<input type=\"text\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

		foreach(explode(' ', 'class id maxlength size style') as $p)
			if(!empty($$p))
				$result .=  " $p=\"{$$p}\"";

		$result .=  " />\n";

		if($has_versioning)
			$result .=  "<br/><small>".ec("Предыдущее значение: ").$previous."</small>\n";

		if($th)
			$result .=  "</td></tr>\n";

		return $result;
	}
}
