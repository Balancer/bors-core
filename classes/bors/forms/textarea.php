<?php

class bors_forms_textarea extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
		if(!$form)
			$form = bors_form::$_current_form;

		extract($params);

		$object = $form->object();
		$value = self::value($params, $form);

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

		$html = '';

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			$html .= "<tr><th>{$th}</th><td>";
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

		$html .= "<textarea name=\"$name\"";
		foreach(explode(' ', 'class id style rows cols') as $p)
			if(!empty($$p))
				$html .= " $p=\"{$$p}\"";

		$html .= ">".htmlspecialchars($value)."</textarea>\n";

		if($has_versioning)
			$html .= "<br/><small>".ec("Исходное значение: ").$previous."</small>\n";

		if($th)
			$html .= "</td></tr>\n";

		return $html;
	}
}
