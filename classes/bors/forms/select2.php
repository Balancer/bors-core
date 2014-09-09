<?php

class bors_forms_select2 extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		$element_id = defval($params, 'dom_id', md5(rand()));
		$value = $this->value();
		$class_name = $params['main_class'];

		// https://github.com/Anahkiasen/html-object
		$input = HtmlObject\Input::hidden($this->property_name(), $value, array(
			'id' => $element_id,
		));

		$edit_params = defval($params, 'edit_params', array());

		jquery_select2::appear_ajax("'#{$element_id}'", $class_name, array_merge($edit_params, array(
			'order' => defval($params, 'order', 'title'),
			'title_field' => defval($params, 'title_field', 'title'),
			'width' => defval($params, 'width' /*, 'resolve'*/),
//			'dropdownAutoWidth' => true,
		)));

		if($value)
		{
			$value_title = object_property(bors_load($class_name, $value), 'title');
			jquery::on_ready("$('#{$element_id}').select2(\"data\", { id: '{$value}', text: \"$value_title\" })");
		}
		else
			jquery::on_ready("$('#{$element_id}').select2(\"data\", { id: '', text: '' })");

		return bors_forms_helper::element_html($input, $params);
	}
}
