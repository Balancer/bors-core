<?php

function smarty_function_input_date($params, &$smarty)
{
	include_once('inc/datetime.php');

	extract($params);

	if(!isset($value))
	{
		$obj = $smarty->get_template_vars('form');
		$value = preg_match('!^\w+$!', $name) ? (isset($value)?$value : ($obj?$obj->$name():NULL)) : '';
	}

//	$obj = $smarty->get_template_vars('form');

	$date = $value; // $obj ? $obj->$name() : NULL;
	if(!$date && !empty($def))
		$date = $def;

	$can_drop = @$can_drop;

	if(!$date)
		$date = $can_drop ? 0 : $GLOBALS['now'];

	$day = 0;
	$mon = 0;
	$yea = 0;
	$hh = 0;
	$mm = 0;
	$ss = 0;

	if($date)
	{
		if(@$params['is_integer'])
		{
			$yea = substr($date, 0, 4);
			$date = substr($date, 4);
			if($date != 0)
			{
				$mon = substr($date, 0, 2);
				$date = substr($date, 2);
			}
			if($date != 0)
			{
				$day = substr($date, 0, 2);
				$date = substr($date, 2);
			}
		}
		elseif(is_numeric($date))
		{
			$day = strftime('%d', $date);
			$mon = strftime('%m', $date);
			$yea = strftime('%Y', $date);
			$hh = strftime('%H', $date);
			$mm = strftime('%M', $date);
			$ss = strftime('%S', $date);
		}
		else
			list($yea, $mon, $day) = explode('-', $date);
	}

	if(empty($year_min))
		$year_min = strftime('%Y') - 20;
	elseif($year_min == 'now')
		$year_min = strftime('%Y');

	$year_min = max(1902, $year_min);// Минимальная корректная UNIXTIME дата - декабрь 1901-го.

	if(empty($params['show_only']))
		$shown = array('y','m','d','h','i','s');
	else
		$shown = explode(',', $params['show_only']);

	// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
	if($th = defval($params, 'th'))
		echo "<tr><th>{$th}</th><td>";

	if(in_array('d', $shown))
	{
		echo "<select name=\"{$name}_day\">\n";
		if($can_drop || !$day)
			echo "<option value=\"0\">--</option>\n";
		for($i = 1; $i<=31; $i++)
			echo "<option".($i==$day?' selected="true"':'').">$i</option>"; //  value=\"$i\"
		echo "</select>\n";

		$shown_days = true;
	}
	else
		$shown_days = false;

	echo "<select name=\"{$name}_month\">\n";
	if($can_drop || !$mon)
		echo "<option value=\"0\">-----</option>\n";
	for($i = 1; $i<=12; $i++)
		echo "<option value=\"$i\"".($i==$mon?' selected="true"':'').">".($shown_days ? bors_lower(month_name_rp($i)) : month_name($i))."</option>";
	echo "</select>\n";

	echo "<select name=\"{$name}_year\">\n";
	if($can_drop || !$yea)
		echo "<option value=\"0\">----</option>\n";
	for($i = strftime('%Y')+1; $i>=$year_min ; $i--)
		echo "<option".($i==$yea?' selected="true"':'').">$i</option>"; // value=\"$i\"
	echo "</select>\n";

	if(!empty($time))
	{
		echo "&nbsp;";

		echo "<select name=\"{$name}_hour\">\n";
		if($can_drop)
			echo "<option value=\"0\">--</option>\n";
		for($i = 0; $i<=23; $i++)
			echo "<option".($i==$hh?' selected="true"':'').">".sprintf('%02d',$i)."</option>"; // value=\"$i\"
		echo "</select>\n";

		echo "<select name=\"{$name}_minute\">\n";
		if($can_drop)
			echo "<option value=\"0\">--</option>\n";
		for($i = 0; $i<=59; $i++)
			echo "<option".($i==$mm?' selected="true"':'').">".sprintf('%02d',$i)."</option>"; //  value=\"$i\"
		echo "</select>\n";
	}

	if($can_drop)
		echo "<input type=\"hidden\" name=\"{$name}_is_fuzzy\" value=\"1\" />\n";

	if(@$params['is_integer'])
		echo "<input type=\"hidden\" name=\"{$name}_is_integer\" value=\"{$params['is_integer']}\" />\n";

	if(@$params['is_utc'])
	{
		echo "TZ=".date_default_timezone_get();
		echo "<input type=\"hidden\" name=\"{$name}_is_utc\" value=\"1\" />\n";
		echo "<input type=\"hidden\" name=\"{$name}_timezone\" value=\"".date_default_timezone_get()."\" />\n";
	}

	$tmv = base_object::template_data('form_time_vars');
	$tmv[] = $name;
	base_object::add_template_data('form_time_vars', $tmv);

	if($th)
		echo "</td></tr>\n";
}
