<?php

function smarty_function_find_var($params, &$smarty)
{
    extract($params);

	$show = empty($var);

	if(empty($find))
		$find = $var;

	$is_3 = method_exists($smarty, 'getTemplateVars');

	if($is_3)
		$x = $smarty->getTemplateVars($find);
	else
		$x = $smarty->get_template_vars($find);

	if($x)
	{
		if($show)
			echo $x;

		return;
	}

	if($is_3)
		$page_this = $smarty->getTemplateVars('this');
	else
		$page_this = $smarty->get_template_vars('this');

	while(true)
	{
		if($value = $page_this->get($find))
			break;

		if($value = config($find))
			break;

		$value = NULL;
		break;
	}

	if($show)
		echo $value;

    $smarty->assign($var, $value);
}
