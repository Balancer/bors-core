<?
    function lt_module($params)
    {
		$class_name = @$params['class'];
		if($class_name)
		{
			$class_name = "module_{$class_name}";
			$obj = object_load($class_name, @$params['id']);
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
