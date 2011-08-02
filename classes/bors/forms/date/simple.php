<?php

class bors_forms_date_simple extends bors_forms_element
{
	static function html($params, &$form = NULL)
	{
		if(!$form)
			$form = bors_form::$_current_form;

		include_once('inc/datetime.php');

		extract($params);

		$object = $form->object();
		$value = self::value($params, $form);

		$html = '';

		$date = $value;

		$can_drop = @$can_drop;

		if(!$date)
			$date = $can_drop ? 0 : $GLOBALS['now'];

		$html .= "<input name=\"$name\" value=\"".date('d.m.Y', $date);

		if(!empty($time))
		{
			$html .= " ".date('H:i', $date);
			if(!empty($seconds))
				$html .= " ".date(':s', $date);
		}

		$html .= '" ';

		if(@$params['time_on_post'])
			$html .= 'disabled="disabled" ';

		$html .= "/>";

		if(@$params['is_integer'])
			$html .= "<input type=\"hidden\" name=\"{$name}_is_integer\" value=\"{$params['is_integer']}\" />";

		if(@$params['time_on_post'])
		{
			template_jquery();
			$html .= ec("&nbsp;<label><input name=\"time_on_post\" type=\"checkbox\" checked=\"checked\" />&nbsp;использовать время отсылки</label>");
			template_js("$(function () { $('input[name=\"time_on_post\"]').change(function() { x=$('input[name=\"$name\"]'); if($(this).attr('checked')) x.attr('disabled', 'disabled'); else x.removeAttr('disabled') }); })");
		}

		$html .= "\n";

		$form->append_attr('time_vars', $name);

		return $html;
	}
}
