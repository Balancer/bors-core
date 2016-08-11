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

		$class = [$this->css('input')];

		$html = '';

		$date = $value;

		if(!$date)
			$date = @$can_drop ? 0 : $GLOBALS['now'];

		$date_string = $date || !$can_drop ? date('d.m.Y', $date) : '';

		if($class)
			$class = "class=\"".addslashes(join(" ", $class))."\"";
		else
			$class = "";


		$html .= "<input type=\"text\" name=\"$name\" $class value=\"".$date_string;

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
			$html .= "<input type=\"hidden\" name=\"{$name}_is_integer\" $class value=\"{$params['is_integer']}\" />";

		if($can_drop)
			$html .= "<input type=\"hidden\" name=\"{$name}_can_drop\" $class value=\"1\" />";

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

		$element_tpl = $form->templater()->get('form_element_html');
		$row_tpl = $form->templater()->get('form_row_html');
		return sprintf($row_tpl, $this->label_html2() , sprintf($element_tpl, $html));
	}
}
