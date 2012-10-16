<?php

function smarty_function_combobox($params, &$smarty)
{
	echo bors_forms_combobox::html($params);
}
