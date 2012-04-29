<?php

class bors_forms_checkbox_list extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
		if(!$form)
			$form = bors_form::$_current_form;

		extract($params);

		$obj = $form->object();
		if(!$obj)
			$obj = $form->calling_object();

		$params = "";
		foreach(explode(' ', 'size style') as $p)
			if(!empty($$p))
				$params .= " $p=\"{$$p}\"";

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
				$current = preg_match('!^\w+$!', $name) ? (isset($value)?$value:($obj?$obj->$name():0)) : 0;
			else
				$current = $obj->$get();

			if(!$current && !empty($list['default']))
				$current = $list['default'];

			if(!is_array($current))
				$current = array($current);
		}
		else
			$current = $values;

		$html = '';

		foreach($list as $id => $iname)
		{
			$ids[] = $id;
			$checked = in_array($id, $current);
			$html .= "<label><input type=\"checkbox\" name=\"".addslashes($name)."[]\" value=\"$id\"".($checked ? " checked=\"checked\"" : "")."$params />".($checked?'<b>':'')."&nbsp;$iname".($checked?'</b>':'')."</label>$delim\n";
		}

		$form->append_attr('checkboxes_list', $name);

		return $html;
	}
}
