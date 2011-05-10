<?php

function smarty_function_bors_radio($params, &$smarty)
{
	extract($params);

	$obj = $smarty->get_template_vars('form');

	$params = "";
	foreach(explode(' ', 'size style') as $p)
		if(!empty($$p))
			$params .= " $p=\"{$$p}\"";

	if(preg_match("!^(\w+)\->(\w+)!", $list, $m))
	{
		if($m[1] == 'this')
			$list = $obj->$list();
		else
			$list = object_load($m[1])->$m[2]();
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

	if(preg_match('/^(\w+)\[\]$/', $name, $m))
	{
		$name = $m[1];
		$is_array = true;
	}
	else
		$is_array = false;

	if(empty($object))
	{
		$current = $obj ? $obj->$name() : @$def;
		$object = "";
	}
	else
	{
		$current = $object->$name();
		$object = $object->internal_uri();
	}

	if($is_array)
		$current = @array_pop($current); // wtf?

	if(!$current && !empty($list['default']))
		$current = $list['default'];

	if(empty($delim))
		$delim = "<br />";

	foreach($list as $id => $iname)
		echo "<label><input type=\"radio\" name=\"{$object}".addslashes($name).($is_array ? '[]' : '')."\" value=\"$id\"".($id == $current ? " checked=\"checked\"" : "")."$params />&nbsp;$iname</label>$delim\n";
}
