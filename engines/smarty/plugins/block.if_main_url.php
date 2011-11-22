<?php

bors_function_include('string/bors_starts_with');

function smarty_block_if_main_url($params, $content, &$smarty)
{
	static $eq = false;

	if($content == NULL) // Открытие блока
	{
		$test_data = url_parse($params['url']);

		$is_3 = method_exists($smarty, 'getTemplateVars');

		if($is_3)
		{
			$page_this = $smarty->getTemplateVars('this');
			$main_uri = $smarty->getTemplateVars('main_uri');
		}
		else
		{
			$page_this = $smarty->get_template_vars('this');
			$main_uri = $smarty->get_template_vars('main_uri');
		}

		if($main_uri)
		{
			$main_data = url_parse($main_uri);
//			echo " bors_starts_with({$main_data['uri']},{$test_data['uri']})";
			if($test_data['path'] == '/')
				return $eq = ($main_data['uri'] == $test_data['uri']);
			else
				return $eq = bors_starts_with($main_data['uri'], $test_data['uri']);
//			return $eq = bors_starts_with($test_data['uri'], $main_data['uri']);
		}

		return $eq = false;
	}

	if($eq)
		echo $content;
}
