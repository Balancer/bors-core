<?php

function smarty_function_module($params, &$smarty)
{
	if(empty($params['name']))
	{
		foreach(explode(' ', 'class id page') as $name)
		{
			$$name = @$params[$name];
			unset($params[$name]);
		}
/*
		if(empty($object))
			$obj = object_load($class, $id, $params);
		else
			$obj = $object;
*/
		$params['page'] = $page;

		if(!$id)
			$id = bors()->main_object();

		$obj = bors_load_ex('module_'.$class, $id, $params);

		if(!$obj)
			$obj = bors_load_ex($class, $id, $params);

		if(!$obj)
			return "Can't load class module '{$class}'";

		if(method_exists($obj, 'html'))
			return $obj->html();

//		if(method_exists($obj, 'html_code'))
//			return $obj->html_code();

		return $obj->body();
	}

	unset($GLOBALS['module_data']);
	$name = $params['name'].".php";
	foreach($params as $key=>$val)
		$GLOBALS['module_data'][$key] = $val;

	ob_start();
	bors_include("modules/$name", true);
	$res = ob_get_contents();
	ob_end_clean();
	return $res;
}
