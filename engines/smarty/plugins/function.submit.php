<?php

function smarty_function_submit($params, &$smarty)
{
	extract($params);

	echo "<input type=\"submit\" value=\"".addslashes($value)."\"";

	foreach(explode(' ', 'class style onClick onclick name') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";

	echo " />";
}
