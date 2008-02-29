<?php
	function smarty_block_form($params, $content, &$smarty)
	{
		extract($params);
	
		$main_obj = $GLOBALS['bors']->main_object();
		
		if(empty($name) && !$main_obj)
		{
	        $smarty->trigger_error("form: empty parameter 'name'");
	        return;
		}
		
		if(empty($name))
			$name = @$class;
			
		if(empty($name))
			$name = get_class($main_obj);
			
		if(empty($id))
			$id = NULL;
			
		$smarty->assign('current_form_class', $form = object_load($name, $id));

		if($main_obj)
			$uri = $main_obj->url();
		else
			$uri = NULL;
			
		if(!$uri)
			$uri = $form->id();
		
		if($content == NULL) // Открытие формы
		{
			if(empty($method))
				$method = 'post';

			if(empty($action))
				$action = $uri;

			if($action == 'this')
				$action = $GLOBALS['main_uri'];
				
			echo "<form enctype=\"multipart/form-data\"";
			
			foreach(split(' ', 'action method name class style enctype') as $p)
				if(!empty($$p))
					echo " $p=\"{$$p}\"";
			
			echo ">\n";
			
			base_object::add_template_data('form_checkboxes', array());
			
			return;
		}
		
		echo $content;
		echo "<input type=\"hidden\" name=\"uri\" value=\"$uri\" />\n";
		if(!empty($ref))
			echo "<input type=\"hidden\" name=\"ref\" value=\"$ref\" />\n";

		if(!empty($act))
			echo "<input type=\"hidden\" name=\"act\" value=\"$act\" />\n";

		if(!empty($subaction))
			echo "<input type=\"hidden\" name=\"subaction\" value=\"$subaction\" />\n";
	
		if(empty($class_name))
			$class_name = $name;
		
		echo "<input type=\"hidden\" name=\"class_name\" value=\"$class_name\" />\n";
		if(!empty($id))
			echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";

		if($cbs = base_object::template_data('form_checkboxes'))
			echo "<input type=\"hidden\" name=\"checkboxes\" value=\"".join(',', $cbs)."\" />\n";
		if($tmv = base_object::template_data('form_time_vars'))
			echo "<input type=\"hidden\" name=\"time_vars\" value=\"".join(',', $tmv)."\" />\n";

		echo "</form>\n";
	}
