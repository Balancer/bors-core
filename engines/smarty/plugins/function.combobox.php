<?php

function smarty_function_combobox($params, &$smarty)
{
	echo bors_form::instance()->element_html('combobox', $params);
}
