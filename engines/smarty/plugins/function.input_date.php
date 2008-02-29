<?php

function smarty_function_input_date($params, &$smarty)
{
	include_once("funcs/datetime.php");
	
	extract($params);
		
	$obj = $smarty->get_template_vars('current_form_class');
	
	$date = intval($obj->$name());
	if(!$date && !empty($def))
		$date = $def;
	
	$can_drop = @$can_drop;
	
	if(!$date)
		$date = $can_drop ? 0 : time();
	
	if($date)
	{
		$day = strftime('%d', $date);
		$mon = strftime('%m', $date);
		$yea = strftime('%Y', $date);
		$hh = strftime('%H', $date);
		$mm = strftime('%M', $date);
		$ss = strftime('%S', $date);
	}
	else
	{
		$day = 0;
		$mon = 0;
		$yea = 0;
		$hh = 0;
		$mm = 0;
		$ss = 0;
	}
	
	if(empty($year_min))
		$year_min = strftime('%Y') - 20;

	echo "<select name=\"{$name}_day\">\n";
	if($can_drop)
		echo "<option value=\"0\">--</option>\n";
	for($i = 1; $i<=31; $i++)
		echo "<option value=\"$i\"".($i==$day?' selected="true"':'').">$i</option>\n";
	echo "</select>";

	echo "<select name=\"{$name}_month\">\n";
	if($can_drop)
		echo "<option value=\"0\">-----</option>\n";
	for($i = 1; $i<=12; $i++)
		echo "<option value=\"$i\"".($i==$mon?' selected="true"':'').">".month_name_rp($i)."</option>\n";
	echo "</select>";
	
	echo "<select name=\"{$name}_year\">\n";
	if($can_drop)
		echo "<option value=\"0\">----</option>\n";
	for($i = strftime('%Y')+1; $i>=$year_min ; $i--)
		echo "<option value=\"$i\"".($i==$yea?' selected="true"':'').">$i</option>\n";
	echo "</select>";

	if(!empty($time))
	{
		echo "&nbsp;";
		
		echo "<select name=\"{$name}_hour\">\n";
		if($can_drop)
			echo "<option value=\"0\">--</option>\n";
		for($i = 0; $i<=23; $i++)
			echo "<option value=\"$i\"".($i==$hh?' selected="true"':'').">".sprintf('%02d',$i)."</option>\n";
		echo "</select>";

		echo "<select name=\"{$name}_minute\">\n";
		if($can_drop)
			echo "<option value=\"0\">--</option>\n";
		for($i = 0; $i<=59; $i++)
			echo "<option value=\"$i\"".($i==$mm?' selected="true"':'').">".sprintf('%02d',$i)."</option>\n";
		echo "</select>";
	}

	$tmv = base_object::template_data('form_time_vars');
	$tmv[] = $name;
	base_object::add_template_data('form_time_vars', $tmv);
}
