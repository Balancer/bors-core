<? 
	function smarty_function_input($params, &$smarty)
	{
		extract($params);
		
		if(!isset($value))
		{
			$obj = $smarty->get_template_vars('current_form_class');
			$value = preg_match('!^\w+$!', $name) ? (isset($value)?$value : ($obj?$obj->$name():NULL)) : '';
		}
			
		if(!isset($value) && isset($def))
			$value = $def;
		
		if(empty($max_length))
			$max_length = 255;
		
		echo "<input type=\"text\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

		foreach(split(' ', 'class style maxlength size') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";
	}
