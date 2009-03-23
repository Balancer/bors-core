<? 
	function smarty_function_checkbox($params, &$smarty)
	{
		extract($params);
		
		if(!isset($value))
		{
			$obj = $smarty->get_template_vars('current_form_class');
			$value = preg_match('!^\w+$!', $name) ? (isset($value)?$value:($obj?$obj->$name():NULL)) : '';

			$cbs = base_object::template_data('form_checkboxes');
			$cbs[] = $name;
			base_object::add_template_data('form_checkboxes', $cbs);
			
			if($value)
				$checked = "checked";
		}
			
		if(!isset($value) && isset($def))
			$value = $def;

		echo "<input type=\"checkbox\"";

		foreach(explode(' ', 'checked name size style value') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";
	}
