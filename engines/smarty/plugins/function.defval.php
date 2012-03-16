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

	if(!bors_templates_smarty::get_var($smarty, $var))
	    $smarty->assign($var, $value);
}
