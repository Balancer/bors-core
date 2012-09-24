<?php

class bors_forms_file extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
		if(!$form)
			$form = bors_form::$_current_form;

		extract($params);

		$obj = $form->object();

		$html = "";

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			$html .= "<tr><th>{$th}</th><td>";
			if(empty($style))
				$style = "width: 99%";
		}

		$html .= "<input type=\"file\" name=\"$name\"";

		foreach(explode(' ', 'class style') as $p)
			if(!empty($$p))
				$html .= " $p=\"{$$p}\"";
		$html .= " />\n";

		if(!empty($file))
			$html .= $file->html();

		if(!empty($id_field))
			$name = "$name=".(empty($class_name_field) ? '' : $class_name_field)."($id_field)";

		$form->append_attr('file_vars', $name);

		if($th)
			$result .=  "</td></tr>\n";

		return $html;
	}
}
