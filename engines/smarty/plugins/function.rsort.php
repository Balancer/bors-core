<? 
	function smarty_function_rsort($params, &$smarty)
	{
    	extract($params);

	    if (empty($var)) 
		{
	        $smarty->trigger_error("rsort: missing 'var' parameter");
    	    return;
	    }

    	if (!in_array('value', array_keys($params))) 
		{
        	$smarty->trigger_error("rsort: missing 'value' parameter");
	        return;
    	}

		rsort($value);

    	$smarty->assign($var, $value);
	}
