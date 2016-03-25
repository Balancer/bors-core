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

		$html = "";

		if(@$td_colspan)
			$td_colspan = " colspan=\"{$td_colspan}\"";
		else
			$td_colspan = "";

		// Если указано, то это заголовок строки таблицы: <tr><th>{$label}</th><td>...code...</td></tr>
		if($label = $this->label())
		{
			$label = preg_replace('!^(.+?) // (.+)$!', "$1<br/><small>$2</small>", $label);

			$html .= "<tr><th class=\"{$this->form()->templater()->form_table_left_th_css()}\">{$label}</th><td{$td_colspan}>";
			if(empty($style) && empty($css))
				$style = "width: 99%";
		}

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

		$html .= "<input type=\"{$type}\" name=\"$input_name\" value=\"".htmlspecialchars($value)."\"";

		foreach(['class', 'id', 'size', 'style', 'placeholder'] as $p)
			if(!empty($$p))
				$html .=  " $p=\"{$$p}\"";

		foreach(['maxlength'] as $p)
		{
			if(empty($params[$p]))
				continue;

			$v = $params[$p];
			if($v != '-')
				$html .=  " $p=\"".htmlspecialchars($v)."\"";
		}

		foreach($html5_data as $key => $val)
			$html .= " data-$key=\"".htmlspecialchars($val)."\"";

		$html .=  " />\n";

		// mbfi/admin/settings
		if(@$previous_value)
			$previous = $previous_value;

		if($has_versioning || $previous)
			$html .=  "<br/><small>".ec("Предыдущее значение: ").$previous."</small>\n";

		if($label)
			$html .=  "</td></tr>\n";

		return $html;
	}
}
