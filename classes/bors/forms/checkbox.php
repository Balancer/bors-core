<?php

class bors_forms_checkbox extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
		if(!$form)
			$form = bors_form::$_current_form;

		extract($params);

		$checked = self::value($params, $form, 'checked');

		if($checked)
			$checked = "checked";

		$form->append_attr('checkboxes', $name);

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
