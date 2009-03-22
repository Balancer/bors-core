<?php

function smarty_function_bors_radio($params, &$smarty)
{
	extract($params);
		
	$obj = $smarty->get_template_vars('current_form_class');
		
	$params = "";
	foreach(split(' ', 'size style') as $p)
		if(!empty($$p))
			$params .= " $p=\"{$$p}\"";

	if(preg_match("!^(\w+)\->(\w+)!", $list, $m))
	{
		if($m[1] == 'this')
			$list = $obj->$list();
		else
			$list = object_load($m[1])->$m[2]();
	}
	else
		$list = object_load($list)->named_list();

	if(empty($object))
	{
		$current = $obj ? $obj->$name() : NULL;
		$object = "";
	}
	else
	{
		$current = $object->$name();
		$object = $object->internal_uri();
	}
	
//	echo "Current for {$object} = '$current'<br />\n";
	
	if(!$current && !empty($list['default']))
		$current = $list['default'];

	if(empty($delim))
		$delim = "<br />";
		
	foreach($list as $id => $iname)
	{
//		echo "$id == $current? -> ".($id == $current)."<br />";
		echo "<input type=\"radio\" name=\"{$object}".addslashes($name)."\" value=\"$id\"".($id == $current ? " checked=\"checked\"" : "")."$params />&nbsp;$iname$delim\n";
	}
}
