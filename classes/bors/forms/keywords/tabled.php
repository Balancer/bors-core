<?php

/*
	$list на входе — массив вида:
	array('категория1' => array('тэг1', 'тэг2', ...), 'категория2' => array(...))
*/

class bors_forms_keywords_tabled extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$keyword_values = $this->value();
		if(!is_array($value))
			$keyword_values = preg_split('/\s*[,;]\s*/', $keyword_values);

		$html = "<table class=\"btab\"><tr>\n";
		$idx = 1;
		foreach(array_keys($list) as $category_name)
			$html .= "\t<th>{$category_name}</th>\n";

		$html .= "</tr><tr>\n";

		$idx = 1;
		foreach($list as $category_name => $category_keywords)
		{
			$columns = ceil(count($category_keywords)/12);
			$html .= "\t<td style=\"column-count: $columns;\" class=\"nobr\">\n";
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

				$html .= "\t<label$style><input name=\"keywords_tabbed[]\" type=\"checkbox\"$checked value=\"{$kw}\" />&nbsp;{$kw}</label><br/>\n";
			}
			$html .= "\t</td>\n";
		}

		$html .= "</tr></table>\n";

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
