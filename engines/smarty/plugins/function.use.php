<?php

function smarty_function_use($params, &$smarty)
{
	if($css = defval($params, 'css'))
		template_css($css);

	if($function = defval($params, 'function'))
		bors_function_include($function);

	$smarty->trigger_error("user: missing type parameter");
}
