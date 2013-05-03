<?php

function smarty_function_file_delete($params, &$smarty)
{
	echo bors_form::instance()->element_html('file_delete', $params);
}
