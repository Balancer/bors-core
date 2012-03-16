<?php

function smarty_function_input_date($params, &$smarty)
{
	echo bors_forms_date::html($params);
}
