<?php

/*
	Параметры для форм по умолчанию
*/

class bors_forms_templates_default extends bors_object
{
	// Если w100p, то распирает формы, типа http://www.balancer.ru/admin/forum/post/3033292/move-tree
	function _input_css_def() { return 'wa'; }
	function _input_css_error_def() { return 'error'; }

	function _textarea_css_def() { return 'w100p'; }
	function _textarea_css_error_def() { return 'error'; }

	function _dropdown_css_error_def() { return 'error'; }

	function _form_table_css_def() { return 'btab w100p'; }
	function _form_table_left_th_css_def() { return 'w33p'; }

	function _form_container_html_def() { return "<table class=\"btab w100p\">%s</table>\n"; }
	function _form_row_html_def() { return "<tr>%s</tr>\n"; }
	function _form_element_label_html_def() { return "<th>%s</th>"; }
	function _form_element_html_def() { return "<td>%s</td>"; }
}
