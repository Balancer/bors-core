<?php

function smarty_function_page_data_array($params, &$smarty)
{
	base_page::add_template_data_array($params['var'], $params['value']);
}
