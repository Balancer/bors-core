<?php

include_once('inc/bors/lists.php');

function smarty_function_dropdown($params, &$smarty)
{
	extract($params);

	$obj = $smarty->get_template_vars('current_form_class');

	if(in_array($name, explode(',', session_var('error_fields'))))
	{
		if(empty($class))
			$class = "error";
		else
			$class .= " error";
	}

	echo "<select";

	foreach(explode(' ', 'size style multiple class onchange') as $p)
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
			$list =&new $list(@$args);
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
		$current = preg_match('!^\w+$!', $name) ? (isset($value) ? $value : ($obj ? $obj->$name() : NULL)) : 0;
	else
		$current = $obj->$get();

	if(!$current && !empty($list['default']))
		$current = $list['default'];

	if(!is_array($current))
		$current = array($current);

	if($is_int)
		for($i=0; $i<count($current); $i++)
			$current[$i] = ($have_null && is_null($current[$i])) ?  NULL : intval($current[$i]);

	foreach($list as $id => $iname)
		if($id !== 'default')
			echo "<option value=\"$id\"".(in_array($id, $current, $strict) ? " selected=\"selected\"" : "").">$iname</option>\n";

	echo "</select>";
}
