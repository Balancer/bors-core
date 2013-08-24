<?php

class bors_forms_date_mixed extends bors_forms_element
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

		if(is_object($value))
			$value = $value->timestamp();

		$html = '';

		$date = $value; // $obj ? $obj->$name() : NULL;
		if(!$date && !empty($def))
			$date = $def;

		$can_drop = @$can_drop;
		$is_fuzzy = @$is_fuzzy;

		if(!$date)
			$date = $GLOBALS['now'];

		$day = 0;
		$mon = 0;
		$yea = 0;
		$hh = 0;
		$mm = 0;
		$ss = 0;

		if($value || ($date && (empty($params['is_integer']))))
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
			$year_min = strftime('%Y') - 100;
		elseif($year_min == 'now')
			$year_min = strftime('%Y');

		$year_min = max(1902, $year_min);// Минимальная корректная UNIXTIME дата - декабрь 1901-го.

		if(empty($year_max))
			$year_max = date('Y') + 1;

		if(empty($params['show_only']))
			$shown = array('y','m','d','h','i','s');
		else
			$shown = explode(',', $params['show_only']);

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
			$html .="<tr><th>{$th}</th><td>";

		if($can_drop && empty($value))
			$type = ' disabled="disabled"';
		else
			$type = '';

		if(in_array('d', $shown))
		{
			$html .="<select name=\"{$name}_day\"$type>\n";
			if($is_fuzzy || !$day)
				$html .="<option value=\"0\">--</option>\n";
			for($i = 1; $i<=31; $i++)
				$html .="<option".($i==$day?' selected="true"':'').">$i</option>"; //  value=\"$i\"
			$html .="</select>\n";

			$shown_days = true;
		}
		else
			$shown_days = false;

		if(in_array('m', $shown))
		{
			$html .="<select name=\"{$name}_month\"$type>\n";
			if($is_fuzzy || !$mon)
				$html .="<option value=\"0\">-----</option>\n";
			for($i = 1; $i<=12; $i++)
				$html .="<option value=\"$i\"".($i==$mon?' selected="true"':'').">".($shown_days ? bors_lower(month_name_rp($i)) : month_name($i))."</option>";
			$html .="</select>\n";
		}

		$html .="<select name=\"{$name}_year\"$type>\n";
		if($is_fuzzy || !$yea)
			$html .="<option value=\"0\">----</option>\n";

		if($year_max > date('Y')+1)
		{
			for($i = $year_min; $i<=$year_max ; $i++)
				$html .="<option".($i==$yea?' selected="true"':'').">$i</option>"; // value=\"$i\"
		}
		else
		{
			for($i = $year_max; $i>=$year_min ; $i--)
				$html .="<option".($i==$yea?' selected="true"':'').">$i</option>"; // value=\"$i\"
		}

		$html .="</select>\n";

		if(!empty($time))
		{
			$html .="&nbsp;";

			foreach(array('hour' => 'hh', 'minute' => 'mm') as $t => $v)
				$html .="<input type=\"text\"
					name=\"{$name}_{$t}\" $type
					value=\"{$$v}\"
					size=\"2\" maxlength=\"2\"
					style=\"width: 3ex\"/>\n";
		}

		if($can_drop)
		{
			template_jquery();
			$html .= ec("&nbsp;<label><input name=\"{$name}_is_null\" type=\"checkbox\"".($can_drop && empty($value) ? " checked=\"checked\"" : "")." />&nbsp;не задано</label>");
			template_js("$(function () {
$('input[name=\"{$name}_is_null\"]').change(function() {
	var f=$(this).is(':checked')
	$('select[name^=\"$name\"]').each(function(){
		el=$(this)
		if(f) el.attr('disabled', 'disabled'); else el.removeAttr('disabled')
	})
	$('input[type=\"text\"][name^=\"$name\"]').each(function(){
		el=$(this)
		if(f) el.attr('disabled', 'disabled'); else el.removeAttr('disabled')
	})
})
})");
		}

		if($is_fuzzy)
			$html .="<input type=\"hidden\" name=\"{$name}_is_fuzzy\" value=\"1\" />\n";

		if(@$params['is_integer'])
			$html .="<input type=\"hidden\" name=\"{$name}_is_integer\" value=\"{$params['is_integer']}\" />\n";

		if(@$params['is_utc'])
		{
			$html .="TZ=".date_default_timezone_get();
			$html .="<input type=\"hidden\" name=\"{$name}_is_utc\" value=\"1\" />\n";
			$html .="<input type=\"hidden\" name=\"{$name}_timezone\" value=\"".date_default_timezone_get()."\" />\n";
		}

		$form->append_attr('time_vars', $name);

		if($th)
			$html .="</td></tr>\n";

		return $html;
	}
}
