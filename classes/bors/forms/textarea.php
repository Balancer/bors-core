<?php

class bors_forms_textarea extends bors_forms_element
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

		if(empty($rows))
			$rows = 7;

		$class = explode(' ', $this->css());
		if(in_array($name, explode(',', session_var('error_fields'))))
			$class[] = $this->css_error();

		if(empty($cols))
			$cols = 60;

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
			if($th == 'def')
			{
				$x = bors_lib_orm::parse_property($form->attr('class_name'), $name);
				$th = $x['title'];
				if(empty($type))
					$type = $x['type'];
			}

			$html .= "<tr><th>{$th}</th><td>";
			if(empty($style))
				$style = "width: 99%";
		}

		if(@$type == 'bbcode')
		{
			static $tmp_id = 0;
			if(!$id)
				$id = 'tmp_id_'.($tmp_id++);

			jquery_markitup::appear('#'.$id);
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
