<? 
	function smarty_function_bors_multiselect($params, &$smarty)
	{
		extract($params);
		
		$obj = $smarty->get_template_vars('current_form_class');
		
		$params = "";
		foreach(split(' ', 'size style') as $p)
			if(!empty($$p))
				$params .= " $p=\"{$$p}\"";
				
		$out = "<select multiple=\"multiple\" name=\"".addslashes($name)."[]\"$params>\n";

	if(preg_match("!^(\w+)\->(\w+)!", $list, $m))
	{
		if($m[1] == 'this')
			$list = $obj->$list();
		else
		{
			$x = object_load($m[1]);

			if(!$x)
				return "Can't load class {$m[1]}";

			$list = $x->$m[2]();
		}
	}
	else
		$list = object_load($list)->named_list();

	$current = $obj->$name();
		
	foreach($list as $id => $iname)
		$out .= "<option value=\"$id\"".(in_array($id, $current) ? " selected=\"selected\"" : "").">$iname</option>\n";
		
	$out .= "</select>";
		
	return $out;
}
