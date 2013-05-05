<?php

/*
	$list на входе — массив вида:
	array('категория1' => array('тэг1', 'тэг2', ...), 'категория2' => array(...))
*/

class bors_forms_keywords_tabbed extends bors_forms_element
{
	function html()
	{
//		template_jquery_js("\$('#keywords_tabbed').tabs()");
		template_jquery_ui_tabs('#keywords_tabbed');

		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$keyword_values = $this->value();
		if(!is_array($value))
			$keyword_values = preg_split('/\s*[,;]\s*/', $keyword_values);

		$html = "<div id=\"keywords_tabbed\">\n\t<ul>\n";
		$idx = 1;
		foreach(array_keys($list) as $category_name)
			$html .= "\t\t<li><a href=\"#keywords_tabbed_".($idx++)."\">{$category_name}</a></li>\n";

		$html .= "\t</ul>\n";

		$idx = 1;
		foreach($list as $category_name => $category_keywords)
		{
			$html .= "\t<div id=\"keywords_tabbed_".($idx++)."\">\n";
			foreach($category_keywords as $kw)
			{
				if(false !== ($pos = array_search($kw, $keyword_values)))
				{
					$checked = ' checked="checked"';
					unset($keyword_values[$pos]);
					$style = " class=\"b\"";
				}
				else
				{
					$checked = '';
					$style = '';
				}

				$html .= "<label$style><input name=\"keywords_tabbed[]\" type=\"checkbox\"$checked value=\"{$kw}\" />&nbsp;{$kw}</label>\n";
			}
			$html .= "\t</div>\n";
		}

		$html .= $form->element_html('input', array(
			'name' => $name,
			'value' => join(', ', $keyword_values),
			'dom_id' => 'keywords',
			'size' => 60,
		));

		$form->append_attr('override_fields', "bors_comma_join({$name}+keywords_tabbed)");

		return $html;
	}
}
