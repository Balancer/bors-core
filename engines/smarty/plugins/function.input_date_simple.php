<?php

function smarty_function_input_date_simple($params, &$smarty)
{
	echo bors_form::instance()->element_html('date_simple', $params);
}
