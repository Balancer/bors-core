<?php

function smarty_function_checkbox_list($params, &$smarty)
{
	echo bors_forms_checkbox_list::html($params);
}
