<?php

function smarty_function_submit($params, &$smarty)
{
	extract($params);

	$out = "";

	if($image_src = defval($params, 'image'))
		$out .= "<input type=\"image\" src=\"".htmlspecialchars($image_src)."\" value=\"".htmlspecialchars($value)."\"";
	else
		$out .= "<input type=\"submit\" value=\"".htmlspecialchars($value)."\"";

	foreach(explode(' ', 'class style onClick onclick name') as $p)
		if(!empty($$p))
			$out .= " $p=\"{$$p}\"";

	$out .= " />";

	if($smarty->get_template_vars('has_autofields'))
		$out = "<tr><th colspan=\"2\">{$out}</th></tr>\n";

	echo $out;
}
