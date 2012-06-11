<?php

function smarty_function_use($params, &$smarty)
{
	if($css = defval($params, 'css'))
	{
		template_css($css);
		return;
	}

	if($function = defval($params, 'function'))
	{
		echo $function;
		bors_function_include($function);
		return;
	}

	bors_throw("use: missing type parameter");
}
