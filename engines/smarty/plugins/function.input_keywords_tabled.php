<?php

function smarty_function_input_keywords_tabled($params, &$smarty)
{
	echo bors_form::instance()->element_html('keywords_tabled', $params);
}
