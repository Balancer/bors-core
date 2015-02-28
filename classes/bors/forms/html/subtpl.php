<?php

class bors_forms_html_subtpl extends bors_forms_element
{
	function html()
	{
/*
'title' => string 'Предварительный просмотр реквизитов' (length=68)
  'type' => string 'html_subtpl' (length=11)
  'tpl_ext' => string 'result' (length=6)
 */

		$params = $this->params();
		$form = $this->form();
		$object = $form->calling_object();

		$class_file = $object->class_file();

		extract($params);

		$tpl = str_replace('.php', ".{$tpl_ext}.tpl", $class_file);

		return bors_templates_smarty::fetch($tpl, array_merge(array(
			'form' => $form,
			'this' => $object,
		), $params));
	}
}
