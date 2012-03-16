<?php

function smarty_function_checkbox($params, &$smarty)
{
	echo bors_forms_checkbox::html($params);
}
