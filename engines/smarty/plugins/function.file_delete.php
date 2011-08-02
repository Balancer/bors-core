<?php

function smarty_function_file_delete($params, &$smarty)
{
	echo bors_forms_file_delete::html($params);
}
