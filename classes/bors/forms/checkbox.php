<?php

class bors_forms_checkbox extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
		if(!$form)
			$form = bors_form::$_current_form;

		extract($params);

		if(!array_key_exists('checked', $params))
		{
			$obj = $form->object();
			$checked = preg_match('!^\w+$!', $name) ? ($obj?$obj->$name():NULL) : '';

			if(!isset($checked) && isset($def))
				$checked = $def;
		}

		if($checked)
			$checked = "checked";

		$cbs = base_object::template_data('form_checkboxes');
		$cbs[] = $name;
		base_object::add_template_data('form_checkboxes', $cbs);

		if(empty($value))
			$value = 1;

		$html = "";

		if($label)
			$html .= "<label>";
		$html .= "<input type=\"checkbox\"";

		foreach(explode(' ', 'checked name size style value') as $p)
			if(!empty($$p))
				$html .= " $p=\"{$$p}\"";

		$html .= " />";

		if(empty($delim))
			$delim = '&nbsp;';
		if(empty($br))
			$br = "<br/>\n";

		if($label)
			$html .= "{$delim}{$label}</label>{$br}";

		$html .= "\n";

		return $html;
	}
}
