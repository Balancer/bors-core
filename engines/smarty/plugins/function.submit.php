<?php

function smarty_function_submit($params, &$smarty)
{
	echo bors_forms_submit::html($params);
}
