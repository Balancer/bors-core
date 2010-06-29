<?php

// Пишем элемент списка, если значение не пустое.
function smarty_function_linz($params, &$smarty)
{
	if(empty($params['value']))
		return '';

	return "<li>{$params['name']} {$params['value']}</li>\n";
}
