<?php

class bors_forms_radio extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
		if(!$form)
			$form = bors_form::$_current_form;

		extract($params);

		$obj = object_property($form, 'object');

		if(!empty($property))
			$name = $property;

		$tag_params = "";
		foreach(explode(' ', 'size style') as $p)
			if(!empty($$p))
				$tag_params .= " $p=\"{$$p}\"";

		if(!is_array($list))
		{
			if(!empty($xref))
			{
				// Задан класс m2m связей
				$xref_obj = new $xref;
				$list = $xref_obj->named_list($obj);
				$name = $xref_obj->name($obj);
			}
			elseif(preg_match("!^(\w+)\->(\w+)!", $list, $m))
			{
				if($m[1] == 'this')
					$list = $obj->$list();
				else
					$list = object_load($m[1])->$m[2]();
			}
			elseif(preg_match("!^\w+$!", $list))
			{
				$list = new $list(@$args);
				$list = $list->named_list();
			}
			else
				eval('$list='.$list);
		}

		if(preg_match('/^(\w+)\[\]$/', $name, $m))
		{
			$name = $m[1];
			$is_array = true;
		}
		else
			$is_array = false;

		if(empty($object))
		{
//			$current = $obj ? $obj->$name() : @$def;
			$current = self::value($params, $form);
			$object = "";
		}
		else
		{
			$current = $object->$name();
			$object = $object->internal_uri();
		}

		if($is_array)
			$current = @array_pop($current); // wtf?

		if(!$current && !empty($list['default']))
			$current = $list['default'];

		unset($list['default']);

		if(empty($delim))
			$delim = "<br />";

		if(in_array($name, explode(',', session_var('error_fields'))))
		{
			if(empty($label_css_class))
				$label_css_class = "error";
			else
				$label_css_class .= " error";
		}

		if(!empty($label_css_class))
			$label_css_class = " class=\"$label_css_class\"";
		else
			$label_css_class = "";

		$colorize = @explode(',', @$pos_colorize);

		$html = '';

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			$html .= "<tr><th>{$th}</th><td>";
			if(empty($style))
				$style = "width: 99%";
		}

		$colorpos = 0;
		foreach($list as $id => $iname)
		{
			$style = array();
			if($color = @$colorize[$colorpos++])
				$style[] = "color: $color";

			if($id == $current && !empty($current_bold))
				$style[] = "font-weight: bold";

			if($style)
				$style = " style=\"".join(";", $style)."\"";
			else
				$style = "";

			$html .= "<label{$label_css_class}{$style}><input type=\"radio\" name=\"{$object}".addslashes($name).($is_array ? '[]' : '')."\" value=\"$id\"".($id == $current ? " checked=\"checked\"" : "")."$tag_params />&nbsp;$iname</label>$delim\n";
		}

		if($th)
			$html .= "</td></tr>\n";

		return $html;
	}
}
