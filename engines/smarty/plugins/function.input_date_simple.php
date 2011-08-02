<?php

function smarty_function_input_date_simple($params, &$smarty)
{
	echo bors_forms_date_simple::html($params);
}
