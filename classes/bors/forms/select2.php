<?php

class bors_forms_select2 extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		$element_id = defval($params, 'dom_id', md5(rand()));
		$value = $this->value();

		$class_name = @$params['class_name'];
		if(empty($class_name))
			$class_name = $params['main_class'];

		if(empty($class_name))
			throw new \Exception("Empty class_name for Select2");

		$data = array(
			'id' => $element_id,
		);

		$width = defval_ne($params, 'width', '100%');

		if($width)
			$data['style'] = 'width:'.$width;

		// https://github.com/Anahkiasen/html-object
		$input = HtmlObject\Input::hidden($this->property_name(), $value, $data);

		$edit_params = defval($params, 'edit_params', []);

		$s2_params = [
			'order' => defval($params, 'order', 'title'),
			'title_field' => defval($params, 'title_field', 'title'),
			'search_fields' => defval($params, 'search_fields', 'title'),
			'where' => defval($params, 'where'),
			'width' => $width,
//			'dropdownAutoWidth' => true,
			'create_new' => defval($params, 'create_new', false),
		];

		jquery_select2::appear_ajax("'#{$element_id}'", $class_name, array_merge($edit_params, $s2_params));

		if($value)
		{
			$value_title = object_property(bors_load($class_name, $value), 'title');
			jquery::on_ready("$('#{$element_id}').select2(\"data\", { id: '".addslashes($value)."', text: \"".addslashes($value_title)."\" })");
		}
//		else
//			jquery::on_ready("$('#{$element_id}').select2(\"data\", { id: '', text: '' })");

		$html = $input;
//		$html = bors_forms_helper::element_html($input, $params);

		// Если указано, то это заголовок строки таблицы: <tr><th>{$label}</th><td>...code...</td></tr>
		if($label = $this->label())
			$html = "<tr><th>{$label}</th><td>$html</td></tr>";

		return $html;
	}
}
