<?php

function smarty_function_dropdown($params, &$smarty)
{
	extract($params);
		
	$obj = $smarty->get_template_vars('current_form_class');
		
	echo "<select";

	foreach(split(' ', 'name size style') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";

	echo ">\n";

//	echo "==={$list}===";

	if(preg_match("!^(\w+)\->(\w+)!", $list, $m))
	{
		if($m[1] == 'this')
			$list = $obj->$m[2]();
		else
			$list = object_load($m[1])->$m[2]();
	}
	else
	{
		$list = &new $list(NULL);
		$list = $list->named_list();
	}
	
	if(empty($get))
		$current = $obj->$name();
	else
		$current = $obj->$get();
		
	if(!$current && !empty($list['default']))
		$current = $list['default'];
		
	foreach($list as $id => $name)
		if($id !== 'default')
			echo "<option value=\"$id\"".($id == $current ? " selected=\"selected\"" : "").">$name</option>\n";
	
	echo "</select>";
}
