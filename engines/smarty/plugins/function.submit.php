<?php

function smarty_function_submit($params, &$smarty)
{
	echo bors_form::instance()->element_html('submit', $params);
}
