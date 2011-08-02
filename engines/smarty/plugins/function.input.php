<?php

function smarty_function_input($params, &$smarty)
{
	echo bors_forms_input::html($params);
}
