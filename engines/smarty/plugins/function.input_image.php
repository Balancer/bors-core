<?php

function smarty_function_input_image($params, &$smarty)
{
	echo bors_form::instance()->element_html('image', $params);
}
