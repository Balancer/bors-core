<?php

// Отработка на:
//	http://admin2.aviaport.wrk.ru/directory/airlines/1/airports/
//		— старый вариант, с bors_form
//	http://admin2.aviaport.wrk.ru/digest/new/
//		— новый вариант, с bors_admin_newstep

class bors_forms_combobox extends bors_forms_element
{
	function html()
	{
		include_once('inc/bors/lists.php');

		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$object = $form->object();
		$html = "";

		$css_classes = array();
		$css_style = array();

		if(in_array($name, explode(',', session_var('error_fields'))))
			$css_classes[] = "error";

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			$html .= "<tr><th>{$th}</th><td>";
			$css_style[] = "width: 99%";
		}

		if(!empty($fixed))
			$html .= "<label><input type=\"radio\" name=\"_{$name}\" style=\"float: left\" value=\"\" />&nbsp;</label>";

		$html .= "<div id=\"{$name}\" style=\"display: inline;\"";

		$class = join(' ', $css_classes);
		$style = join(';', $css_style);

		foreach(explode(' ', 'id size style multiple class onchange') as $p)
			if(!empty($$p))
				$html .= " $p=\"{$$p}\"";

//		if(empty($multiple))
//			$html .= " name=\"{$name}\"";
//		else
//			$html .= " name=\"{$name}[]\"";

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

//		$value = NULL; // $this->value();
		$value = $this->value();

/*
		if(empty($get))
		{
			if(preg_match('!^\w+$!', $name))
				$current =  isset($value) ? $value : ($object ? $object->$name() : NULL);
			else
				$current =  isset($value) ? $value : 0;
		}
		else
			$current = $object->$get();
*/

		if(!$current && !empty($list['default']))
			$current = $list['default'];

		if(empty($current))
			$current = session_var("form_value_{$name}");

		set_session_var("form_value_{$name}", NULL);

		if(!is_array($current))
			$current = array($current);

		if($is_int)
			for($i=0; $i<count($current); $i++)
				$current[$i] = ($have_null && is_null($current[$i])) ?  NULL : intval($current[$i]);

//		foreach($list as $id => $iname)
//			if($id !== 'default')
//				$html .= "\t\t\t<option value=\"$id\"".(in_array($id, $current, $strict) ? " selected=\"selected\"" : "").">$iname</option>\n";

		$html .= "\t\t</div>";

		$attrs = array();

		if(!empty($fixed))
		{
			$html .= "\n<div class=\"clear\">&nbsp;</div>\n";
			foreach($fixed as $t => $v)
			{
				if($v == $value)
					$checked = " checked=\"checked\"";
				else
					$checked = "";
				$html .= "<label><input type=\"radio\" name=\"_{$name}\" value=\"{$v}\"{$checked} />&nbsp;{$t}</label>\n";
			}

			$form->append_attr('override_fields', "!_{$name}");

//			$attrs['onSelect'] = 'function() { alert("Sel="+this.value+", hid="+this.getAttribute("hiddenValue"))  }';
//			$attrs['onSelect'] = 'function() { alert(123) }';
			// Костыль для Firefox
			// http://fairwaytech.com/flexbox
			$attrs['onSelect'] = "function() { \$('input:radio[name=\"_{$name}\"]').first().attr('checked', 'checked'); }";
		}
		else
			$html .= "\n";

		if($th)
			$html .= "</td></tr>\n";

		if(!empty($per_page))
			$attrs['paging'] = array('pageSize' => $per_page);

		if(!empty($width))
			$attrs['width'] = $width;

		if(empty($json))
		{
//			var_dump($params);
			$json = "/_bors/data/lists/{$main_class}.json";
		}

//		http://admin2.aviaport.wrk.ru/directory/aviation/arp/5/
//		http://admin2.aviaport.wrk.ru/digest/new/
		if($value && is_numeric($value))
		{
			$v = bors_load($this->list_class(), $value);
//			var_dump($this->list_class(), $value, $v);
			$attrs['initialValue'] = $v->title();
		}

		jquery_flexbox::appear("'#{$name}'", $json, $attrs);

		return $html;
	}
}
