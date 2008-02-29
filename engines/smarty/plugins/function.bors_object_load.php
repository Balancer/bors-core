<?php

function smarty_function_bors_object_load($params, &$smarty)
{
	$class	= @$params['class'];
	$var	= @$params['var'];
	$show	= @$params['show'];

   	if(!in_array('id', array_keys($params)))
		$id = NULL;
	else
		$id = @$params['id'];
	
	unset($params['class'], $params['id'], $params['var'], $params['show']);

    if(empty($class)) 
	{
   	    $smarty->trigger_error("bors_object_load: missing 'class' parameter");
       	return;
    }

	if(isset($params['page']) && count($params) == 1)
		$params = $params['page'];
	elseif(empty($params))
		$params = 1;

	$obj = object_load($class, $id, $params);

	if($var)
	{
    	$smarty->assign($var, &$obj);
		return;
	}
	
	if($show)
		return $obj->$show();
			
//	return $obj->body();
}
