<?php

function smarty_function_file($params, &$smarty)
{
	echo bors_form::instance()->element_html('file', $params);
}
