<?php

function smarty_function_input_keywords_tabled($params, &$smarty)
{
	echo bors_forms_keywords_tabled::html($params);
}
