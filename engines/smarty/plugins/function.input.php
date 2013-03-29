<?php

function smarty_function_input($params, &$smarty)
{
	echo bors_form::instance()->element_html('input', $params);
}
