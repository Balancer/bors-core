<?php

class bors_forms_file_delete extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
		if(!$form)
			$form = bors_form::$_current_form;

		extract($params);

		$obj = $form->object();

		if(!$obj->$name())
			return;

		return  "<input type=\"checkbox\" name=\"file_{$name}_delete_do\" />&nbsp;$value";
	}
}
