<?php

function smarty_function_checkbox_list($params, &$smarty)
{
	// http://admin2.aviaport.wrk.ru/events/1245/
	echo bors_form::instance()->element_html('checkbox_list', $params);
}
