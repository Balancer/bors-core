<?php

class bors_forms_checkbox_list extends bors_forms_element
{
	// http://admin2.aviaport.wrk.ru/events/1245/

	function html()
	{
		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$obj = $form->object();
		if(!$obj)
			$obj = $form->calling_object();

		$el_params = "";
		foreach(explode(' ', 'size style') as $p)
			if(!empty($$p))
				$el_params .= " $p=\"{$$p}\"";

		if(!empty($xref))
		{
			// Задан класс m2m связей
			$xref_obj = new $xref(NULL);
			$list = $xref_obj->named_list($obj);
			$name = $xref_obj->name($obj, $xref_obj->class_name());
		}

		if(!is_array($list))
		{
			if(preg_match("!^(\w+)\->(\w+)$!", $list, $m))
			{
				if($m[1] == 'this')
					$list = $obj->$m[2]();
				else
					$list = object_load($m[1])->$m[2]();
			}
			elseif(preg_match("!^(\w+)\->(\w+)\('(.+)'\)!", $list, $m))
			{
				if($m[1] == 'this')
					$list = $obj->$m[2]($m[3]);
				else
					$list = object_load($m[1])->$m[2]($m[3]);
			}
			elseif(preg_match("!^\w+$!", $list))
			{
				$list = new $list(@$args);
				$list = $list->named_list();
			}
			else
			{
				eval('$list='.$list);
			}
		}

		if(!$current && !empty($list['default']))
			$current = $list['default'];

		if(empty($delim))
			$delim = "<br />";

		$ids = array();

		if(empty($values))
		{
			if(empty($get))
				$current = preg_match('!^\w+$!', $name) ? (isset($value)?$value:($obj?$obj->get($name):0)) : 0;
			else
				$current = $obj->$get();

			if(!$current && !empty($list['default']))
				$current = $list['default'];

			if(!is_array($current))
				$current = array($current);
		}
		else
			$current = $values;

		$class = explode(' ', defval($params, 'class'));

		if(in_array($name, explode(',', session_var('error_fields'))))
			$class[] = "error";

		if($class)
			$class = ' class="'.join(' ', $class).'"';
		else
			$class = '';

		$html = '';

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			$html .= "<tr><th>{$th}</th><td>";
			if(empty($style))
				$style = "width: 99%";
		}

		if($columns)
		{
			$span = "<div class=\"span".(12/$columns)."\">";
			$html .= "<div class=\"container\"><div class=\"row\">$span";
		}

		$pos = 0;
		foreach($list as $id => $iname)
		{
			$ids[] = $id;
			$checked = in_array($id, $current);
			$html .= "<label><input type=\"checkbox\" name=\"".addslashes($name)."[]\" value=\"$id\"".($checked ? " checked=\"checked\"" : "")."$el_params$class />".($checked?'<b>':'')."&nbsp;$iname".($checked?'</b>':'')."</label>$delim\n";
			$pos++;
			if($columns && $pos >= count($list) / $columns)
			{
				$pos = 0;
				$html .= "</div>$span";
			}
		}

		if($columns)
			$html .= "</div></div>";

		$form->append_attr('checkboxes_list', $name);

		if($th)
			$html .= "</td></tr>\n";

		return $html;
	}
}
