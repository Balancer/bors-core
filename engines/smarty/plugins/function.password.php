<?php

function smarty_function_password($params, &$smarty)
{
	$params['type'] = 'password';
	echo bors_form::instance()->element_html('input', $params);
}
