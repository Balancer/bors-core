<? 
	function smarty_function_file($params, &$smarty)
	{
		extract($params);
		
		$obj = $smarty->get_template_vars('current_form_class');

		echo "<input type=\"file\" name=\"$name\"";

		foreach(split(' ', 'class style') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";
	}
