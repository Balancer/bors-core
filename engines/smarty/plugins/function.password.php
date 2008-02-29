<? 
	function smarty_function_password($params, &$smarty)
	{
		extract($params);
		
		$obj = $smarty->get_template_vars('current_form_class');
		
		$value = $obj->$name();
		
		echo "<input type=\"password\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

		foreach(split(' ', 'class style maxlength size') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";
	}
