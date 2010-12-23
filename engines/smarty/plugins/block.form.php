<?php

/**
	Старый формат. class="..." - имя класса объекта формы
	class_name - имя объекта для передачи в обработчик
*/

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

	$object_class_name = $name;

	$form = object_load($object_class_name, $id);
	if(is_object($form))
		$foo = $form;
	elseif($object_class_name && $object_class_name != 'NULL')
		$foo = new $object_class_name($id);
	else
		$foo = NULL;

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

/*****************************************************************************************/
	if($content == NULL) // Открытие формы
	{
		if(!empty($ajax_validate))
		{
			$form_id = 'form_'.md5(rand());

			template_jquery();
			template_jquery_plugin_css('formvalidator/css/validationEngine.jquery.css');
			template_jquery_plugin('formvalidator/js/jquery.validationEngine-ru.js');
			template_jquery_plugin('formvalidator/js/jquery.validationEngine.js');
			template_js("jQuery(document).ready(function() { jQuery('#{$form_id}').validationEngine()})");

			$smarty->assign('ajax_validate', $ajax_validate);
		}

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

		foreach(explode(' ', 'action method name class style enctype onclick onsubmit target') as $p)
			if(!empty($$p) && ($p != 'name' || $$p != 'NULL'))
				echo " $p=\"{$$p}\"";

		if(!empty($form_id))
			echo " id=\"$form_id\"";

		echo ">\n";

		base_object::add_template_data('form_checkboxes', array());

		if($foo)
			$object_fields = bors_lib_orm::fields($foo);
		else
			$object_fields = array();

		if(!empty($fields))
		{
			$smarty->assign('has_autofields', true);
			echo "<table class=\"btab\">";
			$labels = array();
			if(!is_array($fields))
				$fields = explode(',', $fields);

			foreach($fields as $title => $data)
			{
//				echo "Check $title -> $data<br/>\n";
				if(!is_array($data))
					$data = $object_fields[$property_name = $data];

				$type = $data['type'];
				$title = $data['title'];
				if(!empty($data['class']))
				{
					$type = 'dropdown';
					$class = $data['class'];
				}

				$property_name = $data['name'];

				if(!$title)
					$title = $property_name;

				if($type != 'bool')
					echo "<tr><th>{$title}</th><td>";

				$data['value'] = object_property($form, $property_name);
				$data['class'] = 'w100p';
//				echo "property=$property_name, type=$type, data=".print_d($data).", field=".print_d($field)."<Br/>\n";
				switch($type)
				{
					case 'string':
					case 'input':
						require_once('function.input.php');
						smarty_function_input($data, $smarty);
						break;
					case 'input_date':
						require_once('function.input_date.php');
						smarty_function_input_date(array_merge($data, @$data['args']), $smarty);
						break;
					case 'utime': // UNIX_TIMESTAMP в UTC
						require_once('function.input_date.php');
						set_def($data, 'is_utc', true);
						set_def($data, 'time', true);
						if(!empty($data['args']))
							$data = array_merge($data, $data['args']);
						smarty_function_input_date($data, $smarty);
						break;
					case 'text':
					case 'textarea':
						require_once('function.textarea.php');
						smarty_function_textarea($data, $smarty);
						break;
					case '3state':
						$data['list'] = ec('array("NULL"=>"", 1=>"Да", 0=>"Нет");');
						$data['is_int'] = true;
						require_once('function.dropdown.php');
						smarty_function_dropdown($data, $smarty);
						break;
					case 'dropdown':
						if(array_key_exists('named_list', $data))
						{
							if(preg_match('/^(\w+):(\w+)$/', $data['named_list'], $m))
							{
								$list_class_name = $m[1];
								$id = $m[2];
							}
							else
							{
								$list_class_name = $data['named_list'];
								$id = NULL;
							}
							$list = new $list_class_name($id);	//TODO: статический вызов тут не прокатит, пока не появится повсеместный PHP-5.3.3.
							$data['list'] = $list->named_list();
						}
						else
							$data['list'] = base_list::make($class);

						$data['is_int'] = true;
						foreach($data['list'] as $v => $n)
							$data['is_int'] &= is_numeric($v);

						require_once('function.dropdown.php');
						smarty_function_dropdown($data, $smarty);
						break;
					case 'timestamp_date_droppable':
						$data['can_drop'] = true;
						require_once('function.input_date.php');
						smarty_function_input_date($data, $smarty);
						break;
					case 'image':
						$image = object_load('bors_image', $data['value']);
						echo $image->thumbnail($data['geometry'])->html_code();
						break;
					case 'bool':
						$data['label'] = $title;
						$labels[$property_name] = $data;
				}
				echo "</td></tr>\n";
			}

			if($labels)
			{
				echo "<tr><th>Метки</th><td>";
				require_once('function.checkbox.php');
				foreach($labels as $name => $data)
					smarty_function_checkbox($data, $smarty);
				echo "</td></tr>\n";
			}
		}

		return;
	}

	echo $content;

	// === Закрытие формы ===
	if(!empty($fields))
		echo "</table>";

	if($act == 'skip_all')
	{
		unset($uri);
	}

	if(isset($uri) && $uri != 'NULL')
		echo "<input type=\"hidden\" name=\"uri\" value=\"$uri\" />\n";
	if(isset($ref) && $ref != 'NULL')
		echo "<input type=\"hidden\" name=\"ref\" value=\"$ref\" />\n";

	if(!empty($act))
		echo "<input type=\"hidden\" name=\"act\" value=\"$act\" />\n";

	if(!empty($_GET['inframe']))
		echo "<input type=\"hidden\" name=\"inframe\" value=\"yes\" />\n";

	if(!empty($subaction))
		echo "<input type=\"hidden\" name=\"subaction\" value=\"$subaction\" />\n";

	if(empty($class_name))
	{
		$class_name = $name;
		$go = $uri;
	}
	else
		$go = 'newpage_admin';

	if($class_name && !$id)
		$go = 'newpage_admin';

	if(defval($params, 'go') == 'NULL')	
		$go = NULL;

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
	if($fls = base_object::template_data('form_file_vars'))
		echo "<input type=\"hidden\" name=\"file_vars\" value=\"".join(',', array_unique(array_filter($fls)))."\" />\n";

	if(!base_object::template_data('form_have_go') && $go)
		echo "<input type=\"hidden\" name=\"go\" value=\"$go\" />\n";

	echo "</form>\n";
	base_object::add_template_data('form_checkboxes_list', NULL);
	base_object::add_template_data('form_checkboxes', NULL);
	base_object::add_template_data('form_time_vars', NULL);
	base_object::add_template_data('form_have_go', NULL);
	base_object::add_template_data('form_file_vars', NULL);

	set_session_var('error_fields', NULL);
}
