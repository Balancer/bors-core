<?php

/*
	Параметры для форм по умолчанию
*/

class bors_forms_templates_bootstrap extends bors_forms_templates_default
{
	function _form_table_css_def() { return 'table'; }
	function _form_table_left_th_css_def() { return 'span4'; }

	function _input_css_def() { return 'span8'; }
	function _textarea_css_def() { return 'span8'; }

}
