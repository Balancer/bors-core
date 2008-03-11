<? 
	function smarty_function_checkbox_list($params, &$smarty)
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

	$current = $obj->$name();
		
	if(!$current && !empty($list['default']))
		$current = $list['default'];

	if(empty($delim))
		$delim = "<br />";
	
	$ids = array();
	
	foreach($list as $id => $iname)
	{
		$ids[] = $id;
		$checked = in_array($id, $current);
		echo "<input type=\"checkbox\" name=\"".addslashes($name)."[]\" value=\"$id\"".($checked ? " checked=\"checked\"" : "")."$params />".($checked?'<b>':'')."&nbsp;$iname".($checked?'</b>':'')."$delim\n";
	}

	$vcbs = base_object::template_data('form_checkboxes_list');
	$vcbs[] = $name;
	base_object::add_template_data('form_checkboxes_list', $vcbs);
}
