<? 
	function smarty_function_input($params, &$smarty)
	{
		extract($params);
		
		$obj = $smarty->get_template_vars('current_form_class');
		
		$value = $obj->$name();
		if(empty($value) && !empty($def))
			$value = $def;
		
		if(empty($max_length))
			$max_length = 255;
		
		echo "<input type=\"text\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

		foreach(split(' ', 'class style maxlength size') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";
	}
