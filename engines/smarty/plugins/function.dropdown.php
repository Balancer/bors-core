<?php

include_once('inc/bors/lists.php');

function smarty_function_dropdown($params, &$smarty)
{
	echo bors_form::instance()->element_html('dropdown', $params);
}
