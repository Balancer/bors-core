<?php

function smarty_function_file($params, &$smarty)
{
	extract($params);

	$obj = $smarty->get_template_vars('form');

	echo "<input type=\"file\" name=\"$name\"";

	foreach(explode(' ', 'class style') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";

	echo " />\n";
}
