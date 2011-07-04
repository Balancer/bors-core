<?php

function smarty_function_use($params, &$smarty)
{
	if($css = defval($params, 'css'))
		template_css($css);
}
