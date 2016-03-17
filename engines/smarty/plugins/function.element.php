<?php

function smarty_function_element($params, &$smarty)
{
	echo bors_form::instance()->element_html('element', $params);
}
