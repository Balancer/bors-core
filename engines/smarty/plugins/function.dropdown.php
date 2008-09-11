<?php

function smarty_function_dropdown($params, &$smarty)
{
	extract($params);
		
	$obj = $smarty->get_template_vars('current_form_class');
		
	echo "<select";

	foreach(split(' ', 'size style multiple') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";

	if(empty($multiple))
		echo " name=\"{$name}\"";
	else
		echo " name=\"{$name}[]\"";

	echo ">\n";

//	echo "==={$list}===";

	if(preg_match("!^(\w+)\->(\w+)$!", $list, $m))
	{
		if($m[1] == 'this')
			$list = $obj->$m[2]();
		else
			$list = object_load($m[1])->$m[2]();
	}
	elseif(preg_match("!^(\w+)\->(\w+)\('(.+)'\)!", $list, $m))
	{
//		print_d($m);
		if($m[1] == 'this')
			$list = $obj->$m[2]($m[3]);
		else
			$list = object_load($m[1])->$m[2]($m[3]);
	}
	elseif(preg_match("!^\w+$!", $list))
	{
		$list = &new $list(NULL);
		$list = $list->named_list();
	}
	else
	{
		eval('$list='.$list);
	}
	
	if(empty($get))
		$current = preg_match('!^\w+$!', $name) ? (isset($value)?$value:$obj->$name()) : 0;
	else
		$current = $obj->$get();
		
	if(!$current && !empty($list['default']))
		$current = $list['default'];

	if(!is_array($current))
		$current = array($current);

	foreach($list as $id => $name)
		if($id !== 'default')
			echo "<option value=\"$id\"".(in_array($id, $current) ? " selected=\"selected\"" : "").">$name</option>\n";
	
	echo "</select>";
}
