<?php

function smarty_function_select2($params, &$smarty)
{
	echo bors_form::instance()->element_html('select2', $params);
}
