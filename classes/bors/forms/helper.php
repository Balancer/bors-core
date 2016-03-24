<?php

class bors_forms_helper
{
	static function element_html($element, $params = array())
	{
		// Если указано label, то это заголовок строки таблицы:
		// <tr><th>{$label}</th><td>...code...</td></tr>
		if($label = defval($params, 'label'))
		{
			if($label == 'def')
			{
				$x = bors_lib_orm::parse_property($form->attr('class_name'), $name);
				$label = $x['title'];
			}

			// https://github.com/Anahkiasen/html-object
			$container = HtmlObject\Element::th($label)
				->wrap('tr')
				->setChild($element->wrap('td'));

			return $container;
		}

		return $element;
	}

	static function use_validator()
	{
		jquery::css('/_bors-3rd/bower_components/validationEngine/css/validationEngine.jquery.css');
		jquery::plugin('/_bors-3rd/bower_components/validationEngine/js/languages/jquery.validationEngine-ru.js');
		jquery::plugin('/_bors-3rd/bower_components/validationEngine/js/jquery.validationEngine.js');
		jquery::on_ready("jQuery('#{$dom_form_id}').validationEngine()");
	}
}
