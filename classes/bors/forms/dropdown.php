<?php

class bors_forms_dropdown extends bors_forms_element
{
	function html()
	{
		include_once('inc/bors/lists.php');
		$this->make_data();

		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$dom_id = defval($params, 'dom_id', $id);

		$object = object_property($form, 'object');
		$html = "";

		$class = explode(' ', $this->css());
		if(in_array($name, explode(',', session_var('error_fields'))))
			$class[] = $this->css_error();

		$class = join(' ', $class);

		// Если указано, то это заголовок строки таблицы: <tr><th>{$label}</th><td>...code...</td></tr>
		if($label = defval($params, 'label', defval($params, 'th')))
		{
			if($label == 'def')
			{
				$x = bors_lib_orm::parse_property($form->attr('class_name'), $name);
				$label = $x['title'];
			}

			$html .= "<tr><th>{$label}</th><td>";
			if(empty($style))
				$style = "width: 99%";
		}
		elseif(!empty($params['width']))
			$style = "width: ".$params['width'];

		if(!empty($json) && empty($dom_id))
		{
			$dom_id = md5(rand());
			$tag = "<input type=\"hidden\" style=\"width: 100%\"";
		}
		else
			$tag = "<select";

		$html .= $tag;

		foreach(array('dom_id' => 'id') as $var => $hn)
			if(!empty($$var))
				$html .= " $hn=\"{$$var}\"";

		foreach(explode(' ', 'size style multiple class onchange') as $p)
			if(!empty($$p))
				$html .= " $p=\"{$$p}\"";

		if(empty($multiple))
			$html .= " name=\"{$name}\"";
		else
			$html .= " name=\"{$name}[]\"";

		if(!empty($json))
			$html .= " value=\"".htmlspecialchars($value)."\"";

		$html .= ">\n";

		if(empty($list) && !empty($params['class_name']))
		{
			$list = base_list::make($params['class_name'], array(), array());
		}
		elseif(!is_array($list))
		{
			if(preg_match("!^(\w+)\->(\w+)$!", $list, $m))
			{
				if($m[1] == 'this')
					$list = $object->$m[2]();
				else
					$list = object_load($m[1])->$m[2]();
			}
			elseif(preg_match("!^(\w+)\->(\w+)\('(.+)'\)!", $list, $m))
			{
				if($m[1] == 'this')
					$list = $object->$m[2]($m[3]);
				else
					$list = object_load($m[1])->$m[2]($m[3]);
			}
			elseif(preg_match("!^\w+$!", $list))
			{
				$list = new $list(@$args);
				$list = $list->named_list();
			}
			elseif($list)
				eval('$list='.$list);
			else
				$list = array();
		}
		$have_null = in_array(NULL, $list);
		$strict = defval($params, 'strict', $have_null);
		$is_int = defval($params, 'is_int');

		if(is_null($is_int) && !$strict)
		{
			$is_int = true;
			foreach($list as $k => $v)
				if($k && !is_int($k))
				{
					$is_int = false;
					break;
				}
		}

		$value = $this->value();

		if(empty($get))
		{
			if(preg_match('!^\w+$!', $name))
				$current =  isset($value) ? $value : ($object ? $object->$name() : NULL);
			else
				$current =  isset($value) ? $value : 0;
		}
		else
			$current = $object->$get();

		if(!$current && !empty($list['default']))
			$current = $list['default'];

		unset($list['default']);

		if(empty($current))
			$current = session_var("form_value_{$name}");

		set_session_var("form_value_{$name}", NULL);

		if(!is_array($current))
			$current = array($current);

		if($is_int)
			for($i=0; $i<count($current); $i++)
				$current[$i] = ($have_null && is_null($current[$i])) ?  NULL : intval($current[$i]);

		foreach($list as $id => $iname)
		{
			if(!$id && $have_null)
				$id = 'NULL';

			if($id !== 'default')
				$html .= "\t\t\t<option value=\"$id\"".(in_array($id, $current, $strict) ? " selected=\"selected\"" : "").">$iname</option>\n";
		}

		if(empty($json))
			$html .= "\t\t</select>\n";

		if($label)
			$html .= "</td></tr>\n";

		if(!empty($json))
		{
			jquery_select2::appear_ajax("'#{$dom_id}'", $json, array_merge(defval($params, 'edit_params', array()), array(
				'order' => defval($params, 'order', 'title'),
				'title_field' => defval($params, 'title_field', 'title'),
//				'placeholder' => 'Введите часть названия источника',
			)));

			$value_title = object_property(bors_load($json, $value), 'title');
			jquery::on_ready("$('#{$name}').select2(\"data\", { id: '{$value}', text: \"$value_title\" })");
		}

		return $html;
	}

	function make_data()
	{
		$data = $this->params();
		$form = $this->form();

		if(array_key_exists('list', $data))
		{
			// Ничего не делаем, массив уже в данных.
		}
		elseif(array_key_exists('named_list', $data))
		{
			if(preg_match('/^(\w+):(\w+)$/', $data['named_list'], $m))
			{
				$list_class_name = $m[1];
				$id = $m[2];
			}
			else
			{
				$list_class_name = $data['named_list'];
				$id = NULL;
			}

			$list = new $list_class_name($id);	//TODO: статический вызов тут не прокатит, пока не появится повсеместный PHP-5.3.3.
			$data['list'] = $list->named_list();
		}
		else
		{
			$list_filter = popval($data, 'where', popval($data, 'list_filter', array()));
			if(is_string($list_filter))
				eval("\$list_filter = $list_filter;");

			// $data['main_class'] — http://admin.aviaport.wrk.ru/job/cabinets/236/
			$data['list'] = base_list::make(defval($data, 'main_class', $class), $list_filter, $data);
		}

		// Смешанная проверка для тестирования на http://ucrm.wrk.ru/admin/persons/9/
		if(is_array($data['list']) && ($data['is_int'] = defval($data, 'is_int', true)))
			foreach($data['list'] as $k => $v)
				$data['is_int'] &= !$k || is_numeric($k);

		$this->set_params($data);
	}
}
