<?php

function smarty_function_input_date($params, &$smarty)
{
	echo bors_form::instance()->element_html('date', $params);
}
