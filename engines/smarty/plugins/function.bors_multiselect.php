<?php
function smarty_function_bors_multiselect($params, &$smarty)
{
	extract($params);
		
	$obj = $smarty->get_template_vars('form');

	$params = "";
	foreach(explode(' ', 'size style') as $p)
		if(!empty($$p))
			$params .= " $p=\"{$$p}\"";

	$out = "<select multiple=\"multiple\" name=\"".addslashes($name)."[]\"$params>\n";

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

	$current = $obj ? $obj->$name() : array();

	foreach($list as $id => $iname)
		$out .= "<option value=\"$id\"".(in_array($id, $current) ? " selected=\"selected\"" : "").">$iname</option>\n";
		
	$out .= "</select>";

	$vcbs = base_object::template_data('form_checkboxes_list');
	$vcbs[] = $name;
	base_object::add_template_data('form_checkboxes_list', $vcbs);
		
	return $out;
}
