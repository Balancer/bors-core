<?
    function lt_module($params)
    {
		if($class_name = @$params['class'])
		{
			$class_name = "module_{$class_name}";
			
			foreach(explode(' ', 'id page') as $name)
			{
				$$name = @$params[$name];
				unset($params[$name]);
			}

			$params['page'] = $page;
		
			if(!$id)
				$id = bors()->main_object();
			
			$obj = object_load($class_name, $id, $params);
		
			if($obj)
				return $obj->body();
			else
				return ec('Неизвестный класс ').$class_name;
		}
	
	
//        if(!check_lcml_access('usemodules',true))
//            return $txt;

        $ps = "\$GLOBALS['module_data'] = array(); ";

		foreach($params as $key=>$value)
			$ps .= "\$GLOBALS['module_data']['$key'] = '".addslashes($value)."'; ";

		$out = /*save_format*/("<?php $ps include(\"modules/{$params['url']}.php\"); ?>");
		
		unset($GLOBALS['module_data']);
		
		return $out;
    }
