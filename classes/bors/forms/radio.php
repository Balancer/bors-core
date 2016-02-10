<?php

class bors_forms_radio extends bors_forms_element
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
				$list_class = $list;
				$list = new $list_class(@$args);
				$list = $list->get('named_list');
				if(!$list)
					$list = base_list::make($list_class, array(), $params + array('non_empty' => true));
			}
			else
			{
				if($list)
				{
					require_once(__DIR__.'/../../../inc/bors/lists.php');
					eval('$list='.$list);
				}
			}
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
			$current = $this->value();
			$object = "";
		}
		else
		{
			$current = $object->$name();
			$object = $object->internal_uri();
		}

		if($is_array)
			$current = @array_pop($current); // wtf?

		if(!$current && !empty($list['*default']))
			$current = $list['*default'];

		unset($list['*default']);

		if(empty($delim))
			$delim = "<br />";

		$label_css = explode(' ', defval($params, 'label_css', ''));

		if(in_array($name, explode(',', session_var('error_fields'))))
			$label_css[] = "error";

		if(!empty($label_css))
			$label_css = " class=\"".join(" ", $label_css)."\"";
		else
			$label_css = "";

		$colorize = @explode(',', @$pos_colorize);

		$html = '';

		// Если нужно, добавляем заголовок поля
		$html .= $this->label_html();

		// Если отдельный блок, то на всю ширину.
		if($this->label() && empty($style))
			$style = "width: 99%";

		$colorpos = 0;
		$labels_html = array();

		foreach($list as $id => $iname)
		{
			if(!$iname)
				continue;

			$style = array();
			if($color = @$colorize[$colorpos++])
				$style[] = "color: $color";

			if($id == $current && !empty($current_bold))
				$style[] = "font-weight: bold";

			if($style)
				$style = " style=\"".join(";", $style)."\"";
			else
				$style = "";

			$comment = "";
			if(!empty($comments_in_title))
			{
				if(preg_match('!^(.+)\s+\((.+)\)$!', $iname, $m))
				{
					$iname = $m[1];
					$comment = "<br/><small style=\"color:#999;padding-left: 16px;\">{$m[2]}</small>";
				}
			}

			$labels_html[] = "<label{$label_css}{$style}><input type=\"radio\" name=\"{$object}".addslashes($name).($is_array ? '[]' : '')."\" value=\"$id\"".($id == $current ? " checked=\"checked\"" : "")."$tag_params />&nbsp;$iname$comment</label>$delim";
		}

		$labels_html = join("\n", $labels_html);

		if($container = defval($params, 'label_container'))
			$labels_html = sprintf($container, $labels_html);

		$html .= $labels_html;

//		http://admin.aviaport.ru/news/359785/
//		if($form->get('has_form_table'))
//		if($this->label() && $this->use_tab())
//			$html .= "</td></tr>\n";

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
