<? 
	function smarty_function_image($params, &$smarty)
	{
		extract($params);
		
		$obj = $smarty->get_template_vars('current_form_class');

		if(!$obj->$name())
			return;

		$file = $obj->$name();
		if(preg_match("!\.(gif|jpe?g|png)$!", $file))
			echo "<a href=\"$file\"><img src=\"{$file}\" /></a>\n";
		else
			echo "<a href=\"$file\">{$file}</a>\n";
	}
