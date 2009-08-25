<? 
	function smarty_function_hidden($params, &$smarty)
	{
		extract($params);
		
		if(!isset($value))
		{
			$obj = $smarty->get_template_vars('current_form_class');
			$value = $obj->$name();
		}
			
		if(!isset($value) && isset($def))
			$value = $def;
		
		echo "<input type=\"hidden\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

		foreach(explode(' ', 'class style maxlength size') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";
	}
