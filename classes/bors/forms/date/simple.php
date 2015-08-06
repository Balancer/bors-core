<?php

class bors_forms_date_simple extends bors_forms_element
{
	function html()
	{
		include_once('inc/datetime.php');

		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$object = $form->object();
		$value = $this->value();

		$html = '';

		// Если нужно, добавляем заголовок поля
		$html .= $this->label_html();

		// Если отдельный блок, то на всю ширину.
		if($this->label() && empty($style))
			$style = "width: 99%";

		$date = $value;

		if(!$date)
			$date = @$can_drop ? 0 : $GLOBALS['now'];

		$date_string = $date || !$can_drop ? date('d.m.Y', $date) : '';
		$html .= "<input type=\"text\" name=\"$name\" value=\"".$date_string;

		if(!empty($time))
		{
			$html .= " ".($date || !$can_drop ? date('H:i', $date) : '');
			if(!empty($seconds))
				$html .= $date || !$can_drop ? date(':s', $date) : '';
		}

		$html .= '" ';

		if(@$params['time_on_post'])
			$html .= 'disabled="disabled" ';

		$html .= "/>";

		if(@$params['is_integer'])
			$html .= "<input type=\"hidden\" name=\"{$name}_is_integer\" value=\"{$params['is_integer']}\" />";

		if($can_drop)
			$html .= "<input type=\"hidden\" name=\"{$name}_can_drop\" value=\"1\" />";

		if(@$params['time_on_post'])
		{
			template_jquery();
			$html .= ec("&nbsp;<label><input name=\"time_on_post\" type=\"checkbox\" checked=\"checked\" />&nbsp;использовать время отсылки</label>");
			jquery::on_ready("$('input[name=\"time_on_post\"]').change(function() { x=$('input[name=\"$name\"]'); if($(this).attr('checked')) x.attr('disabled', 'disabled'); else x.removeAttr('disabled') })");
			jquery::plugin('timers');
			jquery::plugin('strftime');
			jquery::on_ready("$('input[name=\"$name\"]').everyTime(1000, function(i) { if($('input[name=\"time_on_post\"]').attr('checked')) $(this).val($.strftime('%d.%m.%Y %H:%M".($seconds ? ':%S' : '')."'))})");
		}

		if($form->get('has_form_table'))
			$html .=  "</td></tr>";

		$html .= "\n";

		$form->append_attr('time_vars', $name);

		return $html;
	}
}
