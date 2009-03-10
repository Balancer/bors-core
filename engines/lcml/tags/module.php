<?php
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

			$ps = array();
			foreach($params as $k => $v)
				$ps[] = '"'.addslashes($k).'" => "'.addslashes($v).'"';

			$result = "<?php
\$obj = object_load(\"$class_name\", \"$id\", array(".join(',', $ps)."));
if(\$obj)
	\$content = \$obj->body();
else
	\$content = ec('Неизвестный класс ').\"$class_name\";
?>";
			return $result;
		}
	
//        if(!check_lcml_access('usemodules',true))
//            return $txt;

        $ps = "\$GLOBALS['module_data'] = array(); ";

		foreach($params as $key=>$value)
			$ps .= "\$GLOBALS['module_data']['$key'] = '".addslashes($value)."'; ";

		$out = "<?php ob_start(); $ps include(\"modules/{$params['url']}.php\"); \$content = ob_get_contents(); ob_clean(); ?>";
		
		unset($GLOBALS['module_data']);
		
		return $out;
    }
