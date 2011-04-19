<?php

include_once('inc/bors/lists.php');

function smarty_function_dropdown($params, &$smarty)
{
	extract($params);

	$obj = $smarty->get_template_vars('form');

	if(in_array($name, explode(',', session_var('error_fields'))))
	{
		if(empty($class))
			$class = "error";
		else
			$class .= " error";
	}

	// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
	if($th = defval($params, 'th'))
	{
		echo "<tr><th>{$th}</th><td>";
		if(empty($tyle))
			$style = "width: 99%";
	}

	echo "<select";

	foreach(explode(' ', 'id size style multiple class onchange') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";

	if(empty($multiple))
		echo " name=\"{$name}\"";
	else
		echo " name=\"{$name}[]\"";

	echo ">\n";

//	echo "==={$list}===";

	if(!is_array($list))
	{
		if(preg_match("!^(\w+)\->(\w+)$!", $list, $m))
		{
			if($m[1] == 'this')
				$list = $obj->$m[2]();
			else
				$list = object_load($m[1])->$m[2]();
		}
		elseif(preg_match("!^(\w+)\->(\w+)\('(.+)'\)!", $list, $m))
		{
			if($m[1] == 'this')
				$list = $obj->$m[2]($m[3]);
			else
				$list = object_load($m[1])->$m[2]($m[3]);
		}
		elseif(preg_match("!^\w+$!", $list))
		{
			$list = new $list(@$args);
			$list = $list->named_list();
		}
		else
		{
			eval('$list='.$list);
		}
	}

	$have_null = in_array(NULL, $list);
	$strict = defval($params, 'strict', $have_null);
	$is_int = defval($params, 'is_int');

	if(empty($get))
	{
		if(preg_match('!^\w+$!', $name))
			$current =  isset($value) ? $value : ($obj ? $obj->$name() : NULL);
		else
			$current =  isset($value) ? $value : 0;
	}
	else
		$current = $obj->$get();

	if(!$current && !empty($list['default']))
		$current = $list['default'];

	if(empty($current))
		$current = session_var("form_value_{$name}");

	set_session_var("form_value_{$name}", NULL);

	if(!is_array($current))
		$current = array($current);

	if($is_int)
		for($i=0; $i<count($current); $i++)
			$current[$i] = ($have_null && is_null($current[$i])) ?  NULL : intval($current[$i]);

	foreach($list as $id => $iname)
		if($id !== 'default')
			echo "<option value=\"$id\"".(in_array($id, $current, $strict) ? " selected=\"selected\"" : "").">$iname</option>\n";

	echo "</select>";

	if($th)
		echo "</td></tr>\n";
}
