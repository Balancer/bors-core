<?php

class bors_forms_file_delete extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$obj = $form->object();

		if(!$obj->$name())
			return;

		return  "<input type=\"checkbox\" name=\"file_{$name}_delete_do\" />&nbsp;$value";
	}
}
