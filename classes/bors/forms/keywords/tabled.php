<?php

/*
	$list на входе — массив вида:
	array('категория1' => array('тэг1', 'тэг2', ...), 'категория2' => array(...))
*/

class bors_forms_keywords_tabled extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
		if(!$form)
			$form = bors_form::$_current_form;

		extract($params);

		$keyword_values = self::value($params, $form);
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
			$html .= "\t<td>\n";
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

				$html .= "\t<label$style><input name=\"keywords_tabbed[]\" type=\"checkbox\"$checked value=\"{$kw}\" />&nbsp;{$kw}</label>\n";
			}
			$html .= "\t</td>\n";
		}

		$html .= "</tr></table>\n";

		$html .= bors_forms_input::html(array(
			'name' => $name,
			'value' => join(', ', $keyword_values),
			'dom_id' => 'keywords',
			'size' => 60,
		), $form);

		$form->append_attr('override_fields', "bors_comma_join({$name}+keywords_tabbed)");

		return $html;
	}
}
