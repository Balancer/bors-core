<?php

function smarty_function_input_keywords_tabbed($params, &$smarty)
{
	echo bors_form::instance()->element_html('keywords_tabbed', $params);
}
