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
		jquery::css('/_bower-asset/jquery-form-validator/form-validator/theme-default.min.css');
		jquery::plugin('/_bors/contrib/jquery-form-validator/ru.js');
		jquery::plugin('/_bower-asset/jquery-form-validator/form-validator/jquery.form-validator.min.js');
		jquery::plugin('/_bower-asset/jquery-form-validator/form-validator/security.js');
		jquery::plugin('/_bower-asset/jquery-form-validator/form-validator/html5.js');
		jquery::on_ready("\$.validate({lang:'ru'})");
	}

	static function validation_check(&$params, &$html5_data)
	{
		extract($params);

		if(!empty($validation))
		{
			$html5_data['validation'] = $validation;
			bors_forms_helper::use_validator();
			$params['maxlength'] = '-';
		}

		foreach(['length', 'allowing', 'confirm', 'url'] as $key)
		{
			if(!empty($params['validation_'.$key]))
			{
				$html5_data['validation-'.$key] = $params['validation_'.$key];
				bors_forms_helper::use_validator();
				$params['maxlength'] = '-';
			}
		}
	}
}
