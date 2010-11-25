<?php

//TODO: в старом коде сделать замены:
//		preAction -> pre_action
//		onAction* -> on_action*

//передать также: 'subaction'

function bors_form_save(&$obj)
{
	if(($post_js = @$_GET['javascript_post_append']))
		session_array_append('javascript_post_append', $post_js);

	if(method_exists($obj, 'on_action'))
	{
		if(method_exists($obj, 'action_target'))
			$obj = $obj->action_target();

		if(!$obj->access())
			return bors_message(ec("Не заданы режимы доступа класса ").get_class($obj)."; access_engine=".$obj->access_engine());

		if(!$obj->access()->can_action(NULL, $_GET))
			return bors_message(ec("[0] Извините, Вы не можете производить операции с этим ресурсом (class=".get_class($obj).", access=".get_class($obj->access()).", method=can_action)"));

		$result = $obj->on_action($_GET);
		if($result === true)
			return true;
	}

	if(!empty($_GET['act']))
	{
		if(method_exists($obj, 'action_target'))
			$obj = $obj->action_target();

		if(!$obj->access())
			return bors_message(ec("Не заданы режимы доступа класса ").get_class($obj)."; access_engine=".$obj->access_engine());

		if(!$obj->access()->can_action($_GET['act'], $_GET))
			return bors_message(ec("[1] Извините, Вы не можете производить операции с этим ресурсом (class=".get_class($obj).", access=".get_class($obj->access()).", method=can_action)"));

		if(method_exists($obj, $method = "on_action_{$_GET['act']}"))
		{
			$result = $obj->$method($_GET);
			if($result === true)
				return true;
		}
	}

	if(!empty($_GET['class_name']) && $_GET['class_name'] != 'NULL')
	{
		$data = $_GET;
		$files = array();

		$files_as_array = true;
		foreach($_FILES as $name => $params)
		{
			foreach($params as $key => $value)
			{
				if(is_array($value))
				{
					foreach($value as $idx => $val2)
					{
						$files[$idx][$key] = $val2;
						$files[$idx]['upload_name'] = $name;
					}
				}
				else
				{
					$files_as_array = false;
					$files[$name][$key] = $value;
				}
			}

			$data['files'] = $files;
			$data['multifiles'] = $files_as_array;
		}

		if(empty($data['checkboxes_list']))
			$checkboxes_list = array();
		else
			$checkboxes_list = explode(',', $data['checkboxes_list']);
		unset($data['checkboxes_list']);

		if(!empty($data['checkboxes']))
		{
			foreach(explode(',', $data['checkboxes']) as $cbn)
			{
				$cbn = str_replace('[]', '', $cbn);
				if(empty($data[$cbn]))
					$data[$cbn] = 0;
			}

			unset($data['checkboxes']);
		}

		foreach($data as $key => $value)
		{
			if(is_array($value) && !in_array($key, $checkboxes_list))
			{
				foreach($value as $idx => $val2)
					$data[$idx][$key] = $val2;
			}
		}

//		print_d($data);

		$form = bors_form_save_object($_GET['class_name'], @$_GET['id'], $data);

		if($form === true)
			return true;

		if(!empty($_GET['go']))
		{
			if($_GET['go'] == "newpage")
				return go($form->url(1));

			if($_GET['go'] == "newpage_admin")
				return go($form->admin_url(1));

			if($_GET['go'] == "newpage_edit_parent" || $_GET['go'] == "admin_parent")
			{
				$p = object_load($form->admin_url(1));
				if($p)
				{
					$p = $p->parents();
					return go($p[0]);
				}

				return go($form->url(1));
			}

			if($form)
			{
				$_GET['go'] = str_replace('%OBJECT_ID%', $form->id(), $_GET['go']);
				$_GET['go'] = str_replace('%OBJECT_URL%', $form->url(), $_GET['go']);
			}
			require_once('inc/navigation.php');
			return go($_GET['go']);
		}
	}

	return false;
}

function bors_form_save_object($class_name, $id, &$data)
{
	if($field = @$data['multiple_check_field'])
		if(empty($data[$field]))
			return;

	if($id)
	{
		$object = object_load($class_name, $id);

		if(!$object)
			$object = object_new($class_name, $id);
	}
	else
		$object = object_new($class_name);

	if(!$object)
		return bors_message(ec("Не могу сохранить объект ")."{$class_name}({$id})");

	$processed = $object->pre_action($data);
	if($processed === true)
		return true;

	if(!$object->access())
		return bors_message(ec("Не заданы режимы доступа класса ").get_class($object)."; access_engine=".$object->access_engine());

	if(!$object->access()->can_action(@$data['act'], $data))
		return bors_message(ec("[2] Извините, Вы не можете производить операции с этим ресурсом (class=".get_class($object).", access=".($object->access_engine())."/".get_class($object->access()).", method=can_action)"));

	if(empty($data['subaction']))
		$method = '';
	else
		$method = '_'.addslashes($data['subaction']);

//	echo "? - $object->$method()";
	if(method_exists($object, $method = 'on_action'.$method))
		if($object->$method($data))
			return true;

	if(($ret = $object->check_data($data)))
		return $ret;

	foreach($data as $key => $val)
	{
		if(!$val || !preg_match("!^file_(\w+)_delete_do$!", $key, $m))
			continue;

		$method = "remove_{$m[1]}_file";
		$object->$method($data);
	}

//	echo "Set fields for $object: ".print_d($data, true).", last=$last{}<br/>"; exit();
	$object->pre_set($data);

//	var_dump($data); exit();
	if(method_exists($object, 'skip_save') && $object->skip_save()) //TODO: костыль для bors_admin_image_append
	{
		if(!$object->set_fields($data, true))
			return true;
	}
	else
	{
		if(!$object->set_fields($data, true))//array_intersect_key($data, bors_lib_orm::all_field_names($object, false)), true))
			return true;
	}

	if($object->has_changed())
	{
		$object->set_modify_time(time(), true);
		$object->set_last_editor_id(bors()->user_id(), true);
	}

	$object->post_set($data);

	if(!$object->id() && method_exists($object, 'new_instance'))
	{
		$object->new_instance();
		$object->on_new_instance($data);
	}

	if($files = @$data['files'])
	{
		if(is_array($files))
		{
			foreach($files as $upload_name => $file)
			{
				if($file['tmp_name'])
					$object->{'upload_'.$upload_name.'_file'}($file, $data);
				elseif(@$file['name'])
					bors_exit(ec("Ошибка загрузки изображения ").$file['name']);
			}
		}
	}

	if(!empty($data['bind_to']) && preg_match('!^(\w+)://(\d+)!', $data['bind_to'], $m))
		$object->add_cross($m[1], $m[2], intval(@$data['bind_order']));

	if(!$object->id() && !(method_exists($object, 'skip_save') && $object->skip_save())) //TODO: костыль для bors_admin_image_append
	{
		if($x = $object->empty_id_handler())
			return $x;
		else
			return debug_exit(ec('Пустой id нового объекта ').$object->class_name().ec('. Возможно нужно использовать function replace_on_new_instance() { return true; }'));
	}

//	echo "Final id: {$object->id()}<br />";

//	bors()->changed_save();

	if($object->has_changed())
		add_session_message(ec('Данные успешно сохранены')/*.print_r(bors()->changed_objects(), true)*/, array('type' => 'success'));

//	echo session_message(array('type' => 'success'));
//	exit( '?');

	return $object;
}

function bors_form_errors($data, $conditions = array())
{
	foreach($data as $field => $value)
		set_session_var("form_value_{$field}", $value);

	foreach($conditions as $error_condition => $fail_message)
	{
		$error_cond = trim($error_condition);

		if(is_array($fail_message))
		{
			$fields = array($fail_message[0]);
			$error_cond = $fail_message[1];
			$fail_message = $fail_message[2];
		}
		elseif(preg_match('/^!(\w+)$/', $error_cond, $m))
		{
			$fields     = array($m[1]);
			$error_cond = empty($data[$m[1]]);
		}
		elseif(preg_match('/^(\w+)\s*!=\s*(\w+)$/', $error_cond, $m))
		{
			$fields     = array($m[1], $m[2]);
			$error_cond = @$data[$m[1]] != @$data[$m[2]];
		}
		else
			throw new Exception("Unknown check_form_data condition string: '$error_condition' => '$fail_message'");

		if($error_cond)
		{
			set_session_var('error_fields', join(',', $fields));
			return $fail_message;
		}
	}

	return NULL;
}
