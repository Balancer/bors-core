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
		$maxlength = defval($params, 'maxlength', 255);

		$object = $form->object();
		$value = $this->value();

		$class = array($this->css());

		if(in_array($name, explode(',', session_var('error_fields'))))
			$class[] = $this->css_error();

		if($id = defval($params, 'dom_id', $id))
		{
			if($label = popval($params, 'label'))
				echo "<label$label_css for=\"$id\">{$label}</label>\n";
		}

		// Если у нас используется валидация данных формы
		if($form->attr('ajax_validate'))
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

		$result = "";

		if(@$td_colspan)
			$td_colspan = " colspan=\"{$td_colspan}\"";
		else
			$td_colspan = "";

		// Если указано, то это заголовок строки таблицы: <tr><th>{$label}</th><td>...code...</td></tr>
		if($label = defval($params, 'label', defval($params, 'th')))
		{
			if($label == 'def')
			{
//				var_dump($form);
//				var_dump($form->attr('class_name'));
				$x = bors_lib_orm::parse_property($form->attr('class_name'), $name);
//				var_dump($x);
				$label = $x['title'];
			}

			$label = preg_replace('!^(.+?) // (.+)$!', "$1<br/><small>$2</small>", $label);

			$result .= "<tr><th class=\"{$this->form()->templater()->form_table_left_th_css()}\">{$label}</th><td{$td_colspan}>";
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

		$result .= "<input type=\"text\" name=\"$input_name\" value=\"".htmlspecialchars($value)."\"";

		foreach(explode(' ', 'class id maxlength size style placeholder') as $p)
			if(!empty($$p))
				$result .=  " $p=\"{$$p}\"";

		$result .=  " />\n";

		// mbfi/admin/settings
		if(@$previous_value)
			$previous = $previous_value;

		if($has_versioning || $previous)
			$result .=  "<br/><small>".ec("Предыдущее значение: ").$previous."</small>\n";

		if($label)
			$result .=  "</td></tr>\n";

		return $result;
	}
}
