<?php

function smarty_function_bors_radio($params, &$smarty)
{
	echo bors_form::instance()->element_html('radio', $params);
}
