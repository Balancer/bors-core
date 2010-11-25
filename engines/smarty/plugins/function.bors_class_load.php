<?php

function smarty_function_bors_class_load($params, &$smarty)
{
	extract($params);

    if(empty($var))
	{
	    $smarty->trigger_error("bors_class_load: missing 'var' parameter");
    	return;
    }

    if(empty($class)) 
	{
	    $smarty->trigger_error("bors_class_load: missing 'class' parameter");
    	return;
    }


	if (!in_array('id', array_keys($params))) {
		$id = NULL;
	}

	echo "smarty: class_load($class, $id)<br />";

	$smarty->assign_by_ref($var, class_load($class, $id));
}
