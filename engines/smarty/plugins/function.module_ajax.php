<?php

function smarty_function_module_ajax($params, &$smarty)
{
	echo bors_modules_ajax::static_html($params);
}
