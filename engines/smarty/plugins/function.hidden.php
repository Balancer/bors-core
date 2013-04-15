<?php
function smarty_function_hidden($params, &$smarty)
{
	extract($params);

	$id = defval($params, 'dom_id', $id);

	if(!isset($value))
	{
		$obj = bors_templates_smarty::get_var($smarty, 'form');
		if(($obj && $obj->id()))
			$value = preg_match('!^\w+$!', $name) ? (isset($value)?$value : ($obj?$obj->$name():NULL)) : '';
		else
			$value = NULL;
	}

	if(!isset($value) && isset($def))
		$value = $def;

	echo "<input type=\"hidden\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

	foreach(explode(' ', 'id class style maxlength size') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";

	echo " />\n";
}
