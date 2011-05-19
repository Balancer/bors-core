<?php

function smarty_function_page_data($params, &$smarty)
{
	base_page::add_template_data($params['var'], $params['value']);
}
