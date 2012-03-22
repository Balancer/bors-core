<?php

function smarty_function_config_set($params, &$smarty)
{
    extract($params);

    if(empty($var))
    {
        $smarty->trigger_error("config_set: missing 'var' parameter");
        return;
    }

    if(!in_array('value', array_keys($params)))
    {
        $smarty->trigger_error("config_set: missing 'value' parameter");
        return;
    }

	config_set($var, $value);
}
