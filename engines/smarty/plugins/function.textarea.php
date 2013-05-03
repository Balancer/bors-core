<?php

function smarty_function_textarea($params, &$smarty)
{
	echo bors_form::instance()->element_html('textarea', $params);
}
