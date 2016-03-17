<?php

// Automatic type by object field name

class bors_forms_field extends bors_forms_element
{
	function html()
	{
		$params = $this->params();
		$form = $this->form();

		$field = bors_lib_orm::parse_property($form->attr('class_name'), $params['name']);

//		r($field);
		if(empty($params['label']))
			$params['label'] = $field['title'];

		echo $form->element_html_by_field_type($field['type'], $params);
	}
}
