<?
    function lt_module($params)
    { 
//        if(!check_lcml_access('usemodules',true))
//            return $txt;

        $ps = "\$GLOBALS['module_data'] = array(); ";

		foreach($params as $key=>$value)
			$ps .= "\$GLOBALS['module_data']['$key'] = '".addslashes($value)."'; ";

		$out = /*save_format*/("<?php $ps include(\"modules/{$params['url']}.php\"); ?>");
		
		unset($GLOBALS['module_data']);
		
		return $out;
    }
