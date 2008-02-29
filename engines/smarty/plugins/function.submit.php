<? 
	function smarty_function_submit($params, &$smarty)
	{
		extract($params);
		
		echo "<input type=\"submit\" value=\"".addslashes($value)."\"";

		foreach(split(' ', 'class style') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />";
	}
