<?php

/*
	$list на входе — массив вида:
	array('категория1' => array('тэг1', 'тэг2', ...), 'категория2' => array(...))
*/

class bors_forms_keywords_tabbed extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
//		template_jquery_js("\$('#keywords_tabbed').tabs()");
		template_jquery_ui_tabs('#keywords_tabbed');
		if(!$form)
			$form = bors_form::$_current_form;

		extract($params);

		$keywords = self::value($params, $form);
		if(!is_array($value))
		{
			$keywords = preg_split('/[,;]\s*/', $keywords);
		}

		$html = "<div id=\"keywords_tabbed\">\n\t<ul>\n";
		$idx = 1;
		foreach(array_keys($list) as $category_name)
			$html .= "\t\t<li><a href=\"#keywords_tabbed_".($idx++)."\">{$category_name}</a></li>\n";

		$html .= "\t</ul>\n";

		$idx = 1;
		foreach($list as $category_name => $keywords)
		{
			$html .= "\t<div id=\"keywords_tabbed_".($idx++)."\">\n";
			foreach($keywords as $kw)
				$html .= "<label><input name=\"keywords_tabbed[]\" type=\"checkbox\" />&nbsp;{$kw}</label>\n";
			$html .= "\t</div>\n";
		}

		return $html;
	}
}
