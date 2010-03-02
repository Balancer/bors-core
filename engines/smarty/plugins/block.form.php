<?php
	function smarty_block_form($params, $content, &$smarty)
	{
		extract($params);

		$main_obj = bors()->main_object();

		if(empty($name) && !$main_obj)
		{
	        $smarty->trigger_error("form: empty parameter 'name'");
	        return;
		}

		if(empty($name))
			$name = @$class;

		if(!empty($object))
		{
			$name = $object->class_name();
			$id   = $object->id();
		}

		if(empty($name) || $name == 'this')
		{
			$name = $main_obj->class_name();
			if(empty($id))
				$id = $main_obj->id() ;
		}

		if(empty($id) || $id == 'NULL')
			$id = NULL;

		$form = object_load($name, $id);
		$smarty->assign('current_form_class', $form);
		$smarty->assign('form', $form);

		if(!isset($uri))
		{
			if($main_obj)
				$uri = $main_obj->called_url();
			else
				$uri = NULL;

			if(!$uri)
				$uri = $form->id();
		}

		if($content == NULL) // Открытие формы
		{
			$class = @$css_class;

			if(empty($method))
				$method = 'post';

			if(empty($action))
				$action = $uri;

			if($action == 'this')
				$action = $GLOBALS['main_uri'];

			if($action == 'target')
				$action = $form->url();

			echo "<form enctype=\"multipart/form-data\"";

			foreach(explode(' ', 'action method name class style enctype') as $p)
				if(!empty($$p) && ($p != 'name' || $$p != 'NULL'))
					echo " $p=\"{$$p}\"";

			echo ">\n";

			base_object::add_template_data('form_checkboxes', array());

			return;
		}

		echo $content;

		// === Закрытие формы ===
		if(isset($uri) && $uri != 'NULL')
			echo "<input type=\"hidden\" name=\"uri\" value=\"$uri\" />\n";
		if(isset($ref) && $ref != 'NULL')
			echo "<input type=\"hidden\" name=\"ref\" value=\"$ref\" />\n";

		if(!empty($act))
			echo "<input type=\"hidden\" name=\"act\" value=\"$act\" />\n";

		if(!empty($subaction))
			echo "<input type=\"hidden\" name=\"subaction\" value=\"$subaction\" />\n";

		if(empty($class_name))
			$class_name = $name;

		if(!empty($class_name) && $class_name != 'NULL' && $class_name != 'this')
			echo "<input type=\"hidden\" name=\"class_name\" value=\"$class_name\" />\n";

		if(!empty($id) && $id != 'NULL')
			echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";

		if(($cbs = base_object::template_data('form_checkboxes')) && empty($no_auto_checkboxes))
			echo "<input type=\"hidden\" name=\"checkboxes\" value=\"".join(',', array_unique(array_filter($cbs)))."\" />\n";
		if(($vcbs = base_object::template_data('form_checkboxes_list')) && empty($no_auto_checkboxes))
			echo "<input type=\"hidden\" name=\"checkboxes_list\" value=\"".join(',', array_unique(array_filter($vcbs)))."\" />\n";
		if($tmv = base_object::template_data('form_time_vars'))
			echo "<input type=\"hidden\" name=\"time_vars\" value=\"".join(',', array_unique(array_filter($tmv)))."\" />\n";

		if(!base_object::template_data('form_have_go'))
			echo "<input type=\"hidden\" name=\"go\" value=\"newpage_admin\" />\n";

		echo "</form>\n";
		base_object::add_template_data('form_checkboxes_list', NULL);
		base_object::add_template_data('form_checkboxes', NULL);
		base_object::add_template_data('form_time_vars', NULL);
		base_object::add_template_data('form_have_go', NULL);
	}
