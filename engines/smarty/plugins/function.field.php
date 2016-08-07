<?php

function smarty_function_field($params, &$smarty)
{
	echo bors_form::instance()->element_html('field', $params);
}
