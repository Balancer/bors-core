<? 
	function smarty_function_textarea($params, &$smarty)
	{
		extract($params);
		
		$obj = $smarty->get_template_vars('current_form_class');
		
		$value = $obj->$name();

		if(empty($rows))
			$rows = 7;

		if(empty($cols))
			$cols = 50;
		
		echo "<textarea name=\"$name\"";
		foreach(split(' ', 'class style rows cols') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";
		
		echo ">".htmlspecialchars($value)."</textarea>\n";
	}
