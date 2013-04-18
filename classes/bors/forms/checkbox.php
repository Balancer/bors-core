<?php

class bors_forms_checkbox extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$checked = $this->value($params, $form, 'checked');

		if($checked)
			$checked = "checked";

		$form->append_attr('checkboxes', $name);

		if(empty($value))
			$value = 1;

		$html = "";

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
			$html .= "<tr><th>{$th}</th><td>";

		if(!empty($label_css_class))
			$label_css_class = " class=\"$label_css_class\"";
		else
			$label_css_class = "";

		if($label)
			$html .= "<label{$label_css_class}>";

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

		if($th)
			$html .=  "</td></tr>\n";

		$html .= "\n";

		return $html;
	}
}
