<?php

function smarty_function_submit($params, &$smarty)
{
	extract($params);

	if($image_src = defval($params, 'image'))
		echo "<input type=\"image\" src=\"".htmlspecialchars($image_src)."\" value=\"".htmlspecialchars($value)."\"";
	else
		echo "<input type=\"submit\" value=\"".htmlspecialchars($value)."\"";

	foreach(explode(' ', 'class style onClick onclick name') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";

	echo " />";
}
