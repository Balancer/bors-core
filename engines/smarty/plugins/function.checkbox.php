<? 
	function smarty_function_checkbox($params, &$smarty)
	{
		extract($params);

		if(!array_key_exists('checked', $params))
		{		
			$obj = $smarty->get_template_vars('current_form_class');
			$checked = preg_match('!^\w+$!', $name) ? ($obj?$obj->$name():NULL) : '';
		
			if(!isset($checked) && isset($def))
				$checked = $def;
		}

		if($checked)
			$checked = "checked";

		$cbs = base_object::template_data('form_checkboxes');
		$cbs[] = $name;
		base_object::add_template_data('form_checkboxes', $cbs);

		if(empty($value))
			$value = 1;

		echo "<input type=\"checkbox\"";

		foreach(explode(' ', 'checked name size style value') as $p)
			if(!empty($$p))
				echo " $p=\"{$$p}\"";

		echo " />\n";
	}
