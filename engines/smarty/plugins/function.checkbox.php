<? 
	function smarty_function_checkbox($params, &$smarty)
	{
		extract($params);
		
		$obj = $smarty->get_template_vars('current_form_class');

		$cbs = base_object::template_data('form_checkboxes');
		$cbs[] = $name;
		base_object::add_template_data('form_checkboxes', $cbs);
		
		echo "<input type=\"checkbox\"";

		foreach(split(' ', 'name size style') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		if($obj->$name())
			echo " checked=\"true\"";

		echo " />\n";
	}
