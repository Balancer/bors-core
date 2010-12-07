<?php

function smarty_function_input_date_simple($params, &$smarty)
{
	include_once('inc/datetime.php');

	extract($params);

	if(!isset($value))
	{
		$obj = $smarty->get_template_vars('current_form_class');
		$value = preg_match('!^\w+$!', $name) ? (isset($value)?$value : ($obj?$obj->$name():NULL)) : '';
	}

	$date = $value;
	if(!$date && !empty($def))
		$date = $def;

	$can_drop = @$can_drop;

	if(!$date)
		$date = $can_drop ? 0 : $GLOBALS['now'];

	echo "<input name=\"$name\" value=\"".date('d.m.Y', $date);

	if(!empty($time))
	{
		echo " ".date('H:i', $date);
		if(!empty($seconds))
			echo " ".date(':s', $date);
	}

	echo "\" />";

	if(@$params['is_integer'])
		echo "<input type=\"hidden\" name=\"{$name}_is_integer\" value=\"{$params['is_integer']}\" />\n";

	$tmv = base_object::template_data('form_time_vars');
	$tmv[] = $name;
	base_object::add_template_data('form_time_vars', $tmv);
}
