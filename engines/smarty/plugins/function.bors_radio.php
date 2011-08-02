<?php

function smarty_function_bors_radio($params, &$smarty)
{
	echo bors_forms_radio::html($params);
}
