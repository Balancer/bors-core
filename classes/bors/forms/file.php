<?php

class bors_forms_file extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
		if(!$form)
			$form = bors_form::$_current_form;

		extract($params);

		$obj = $form->object();

		$html = "<input type=\"file\" name=\"$name\"";

		foreach(explode(' ', 'class style') as $p)
			if(!empty($$p))
				$html .= " $p=\"{$$p}\"";

		$html .= " />\n";


		if(!empty($id_field))
			$name = "$name=".(empty($class_name_field) ? '' : $class_name_field)."($id_field)";

		$form->append_attr('file_vars', $name);

		return $html;
	}
}
