<? 
	function smarty_function_go($params, &$smarty)
	{
		echo "<input type=\"hidden\" name=\"go\" value=\"".addslashes($params['value'])."\" />\n";
	}
