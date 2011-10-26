<?php

function smarty_function_checkbox_list($params, &$smarty)
{
	echo bors_forms_checkbox_list::html($params);
	return;

	extract($params);

	$obj = $smarty->get_template_vars('form');
//var_dump($smarty);
	$params = "";
	foreach(explode(' ', 'size style') as $p)
		if(!empty($$p))
			$params .= " $p=\"{$$p}\"";

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

	if(!$current && !empty($list['default']))
		$current = $list['default'];

	if(empty($delim))
		$delim = "<br />";

	$ids = array();

	if(empty($values))
	{
		if(empty($get))
			$current = preg_match('!^\w+$!', $name) ? (isset($value)?$value:($obj?$obj->$name():0)) : 0;
		else
			$current = $obj->$get();

		if(!$current && !empty($list['default']))
			$current = $list['default'];

		if(!is_array($current))
			$current = array($current);
	}
	else
		$current = $values;

	foreach($list as $id => $iname)
	{
		$ids[] = $id;
		$checked = in_array($id, $current);
		echo "<label><input type=\"checkbox\" name=\"".addslashes($name)."[]\" value=\"$id\"".($checked ? " checked=\"checked\"" : "")."$params />".($checked?'<b>':'')."&nbsp;$iname".($checked?'</b>':'')."</label>$delim\n";
	}

	$vcbs = base_object::template_data('form_checkboxes_list');
	$vcbs[] = $name;
	base_object::add_template_data('form_checkboxes_list', $vcbs);
}
