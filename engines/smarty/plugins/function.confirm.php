<?php

function smarty_function_confirm($params, &$smarty)
{
	extract($params);

	echo "[ <a href=\"{$yes_link}\">Да</a> | <a href=\"{$no_link}\">Нет</a> ]";
}
