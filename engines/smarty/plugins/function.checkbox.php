<?php

function smarty_function_checkbox($params, &$smarty)
{
	echo bors_form::instance()->element_html('checkbox', $params);
}
