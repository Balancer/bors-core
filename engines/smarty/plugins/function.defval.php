<?php

/**
	Присваивает значение переменной, если оно не определено
*/

function smarty_function_defval($params, &$smarty)
{
    extract($params);

    if(empty($var))
    {
        $smarty->trigger_error("defval: missing 'var' parameter");
        return;
    }

    if(!in_array('value', array_keys($params)))
    {
        $smarty->trigger_error("defval: missing 'value' parameter");
        return;
    }

	if(!$smarty->get_template_vars($var))
	    $smarty->assign($var, $value);
}
