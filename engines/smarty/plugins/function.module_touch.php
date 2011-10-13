<?php

function smarty_function_module_touch($params, &$smarty)
{
	echo bors_module_touch::html($params);
}
