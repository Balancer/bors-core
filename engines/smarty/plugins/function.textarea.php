<?php

function smarty_function_textarea($params, &$smarty)
{
	echo bors_forms_textarea::html($params);
}
