<?php

class bors_forms_input extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$object = $form->object();
		$value = $this->value();

		$class = [$this->css()];
		$html5_data = [];

		if(in_array($name, explode(',', session_var('error_fields'))))
			$class[] = $this->css_error();

		if($id = defval($params, 'dom_id', $id))
		{
			if($label = popval($params, 'label'))
				echo "<label$label_css for=\"$id\">{$label}</label>\n";
		}

		$type = defval($params, 'type', 'text');

		// Если у нас используется валидация данных формы
		if($form->attr('ajax_validate'))
		{
			bors_debug::syslog('warning/obsolete', 'Use old `ajax_validate` property. Change it to `validation`');

			if(empty($id))
			{
				static $input_id = 0;
				$id = 'input_id_'.($input_id++);
			}

			if(!empty($ajax_validator))
				$class[] = 'validate['.$ajax_validator.']';
		}

		bors_forms_helper::validation_check($params, $html5_data);

//		class="validate[required,custom[noSpecialCaracters],length[5,20]]"
		$versioning = object_property($object, 'versioning_properties', []);

		if(array_key_exists($name, $versioning))
		{
			$has_versioning = true;
			$previous = $versioning[$name];
			$class[] = 'yellow_box';
		}
		else
			$has_versioning = false;

		if(empty($input_name))
			$input_name = $name;

		if($inset = defval($params, 'inset'))
		{
			if($inset == $value || !$value)
			{
				$value = $inset;
				$class[] = 'inset';
			}
		}

		$class = join(' ', $class);

		$element_html = "<input type=\"{$type}\" name=\"$input_name\" value=\"".htmlspecialchars($value)."\"";

		foreach(['class', 'id', 'size', 'style', 'placeholder'] as $p)
			if(!empty($$p))
				$element_html .=  " $p=\"{$$p}\"";

		foreach(['maxlength'] as $p)
		{
			if(empty($params[$p]))
				continue;

			$v = $params[$p];
			if($v != '-')
				$element_html .=  " $p=\"".htmlspecialchars($v)."\"";
		}

		foreach($html5_data as $key => $val)
			$element_html .= " data-$key=\"".htmlspecialchars($val)."\"";

		$element_html .=  " />\n";

		// mbfi/admin/settings
		if(@$previous_value)
			$previous = $previous_value;

		if($has_versioning || $previous)
			$element_html .=  "<br/><small>".ec("Предыдущее значение: ").$previous."</small>\n";

		$element_tpl = $form->templater()->get('form_element_html');
		$row_tpl = $form->templater()->get('form_row_html');
		return sprintf($row_tpl, $this->label_html2(), sprintf($element_tpl, $element_html));
	}
}
