<?php

function smarty_function_icon($params, &$smarty)
{
	$params['smarty'] = $smarty;
	return bors_html::icon($params);
}
