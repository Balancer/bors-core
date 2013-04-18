<?php

class bors_forms_dropdown extends bors_forms_element
{
	function html()
	{
		include_once('inc/bors/lists.php');

		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$id = defval($params, 'dom_id', $id);

		$object = object_property($form, 'object');
		$html = "";

		$class = explode(' ', $this->css());
		if(in_array($name, explode(',', session_var('error_fields'))))
			$class[] = $this->css_error();

		$class = join(' ', $class);

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			if($th == 'def')
			{
				$x = bors_lib_orm::parse_property($form->attr('class_name'), $name);
				$th = $x['title'];
			}

			$html .= "<tr><th>{$th}</th><td>";
			if(empty($style))
				$style = "width: 99%";
		}

		$html .= "<select";

		foreach(explode(' ', 'id size style multiple class onchange') as $p)
			if(!empty($$p))
				$html .= " $p=\"{$$p}\"";

		if(empty($multiple))
			$html .= " name=\"{$name}\"";
		else
			$html .= " name=\"{$name}[]\"";

		$html .= ">\n";

		if(!is_array($list))
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
			$is_int = true;

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
			if($id !== 'default')
				$html .= "\t\t\t<option value=\"$id\"".(in_array($id, $current, $strict) ? " selected=\"selected\"" : "").">$iname</option>\n";

		$html .= "\t\t</select>\n";

		if($th)
			$html .= "</td></tr>\n";

		return $html;
	}
}
