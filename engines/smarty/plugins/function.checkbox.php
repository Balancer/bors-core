<? 
	function smarty_function_checkbox($params, &$smarty)
	{
		extract($params);
		
		if(!isset($value))
		{
			$obj = $smarty->get_template_vars('current_form_class');
			$value = preg_match('!^\w+$!', $name) ? (isset($value)?$value:$obj->$name()) : '';

			$cbs = base_object::template_data('form_checkboxes');
			$cbs[] = $name;
			base_object::add_template_data('form_checkboxes', $cbs);
		}
			
		if(!isset($value) && isset($def))
			$value = $def;

		echo "<input type=\"checkbox\"";

		foreach(split(' ', 'name size style') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		if($value)
			echo " checked=\"true\"";

		echo " />\n";
	}
