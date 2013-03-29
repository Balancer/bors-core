<?php

/*
	Параметры для форм по умолчанию
*/

class bors_forms_templates_default extends bors_object
{
	function _input_css_def() { return 'w100p'; }
	function _input_css_error_def() { return 'error'; }

	function _textarea_css_def() { return 'w100p'; }

	function _form_table_css_def() { return 'btab w100p'; }
	function _form_table_left_th_css_def() { return 'w33p'; }
}
