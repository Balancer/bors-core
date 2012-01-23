<?php
    function lt_module($params)
    {
		if($class_name = @$params['class'])
		{
			if(class_include($mcn = "module_{$class_name}"))
				$class_name = $mcn;

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
\$obj = bors_load_ex(\"$class_name\", \"$id\", array(".join(',', $ps)."));
if(\$obj)
	\$content = method_exists(\$obj, 'html_code') ? \$obj->html_code() : \$obj->html();
else
	\$content = ec('Неизвестный класс «').\"$class_name\".ec(\"»\");
?>";
			return save_format($result);
		}

//        if(!check_lcml_access('usemodules',true))
//            return $txt;

        $ps = "\$GLOBALS['module_data'] = array(); ";

		foreach($params as $key=>$value)
			if(!is_object($value))
				$ps .= "\$GLOBALS['module_data']['$key'] = '".addslashes($value)."'; ";

		$out = "<?php ob_start(); $ps include(\"modules/{$params['url']}.php\"); \$content = ob_get_contents(); ob_clean(); ?>";

		unset($GLOBALS['module_data']);

		return save_format($out);
    }
