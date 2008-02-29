<? 
	function smarty_function_hidden($params, &$smarty)
	{
		extract($params);
		
		$obj = $smarty->get_template_vars('current_form_class');
		
		$value = is_numeric($value) ? $value : $obj->$name();
		
		echo "<input type=\"hidden\" name=\"$name\" value=\"".htmlspecialchars($value)."\"";

		foreach(split(' ', 'class style maxlength size') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";
	}
