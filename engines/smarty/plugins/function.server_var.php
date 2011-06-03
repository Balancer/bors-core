<?php

function smarty_function_server_var($params, &$smarty)
{
    extract($params);

    if(empty($name))
    {
        $smarty->trigger_error("server_val: missing 'name' parameter");
        return;
    }

	echo bors_server_var($name, @$default);
}
