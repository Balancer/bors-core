<?php

//TODO: в старом коде сделать замены:
//		preAction -> pre_action
//		onAction* -> on_action*

//передать также: 'subaction'

function bors_form_save(&$obj)
{
//	if(debug_is_balancer()) { echo $obj; print_d($_GET); exit(); }

	if(!empty($_GET['act']))
	{
		if(!$obj->access())
			return bors_message(ec("Не заданы режимы доступа класса ").get_class($obj)."; access_engine=".$obj->access_engine());

		if(!$obj->access()->can_action($_GET['act']))
			return bors_message(ec("[1] Извините, Вы не можете производить операции с этим ресурсом (class=".get_class($obj).", access=".get_class($obj->access()).", method=can_action)"));

		if(method_exists($obj, $method = "on_action_{$_GET['act']}"))
		{
			$result = $obj->$method($_GET);
			if($result === true)
				return true;
		}
	}

//	if(debug_is_balancer()) { print_d($_GET); exit(); }

	if(!empty($_GET['class_name']) && $_GET['class_name'] != 'NULL')
	{
		$objects_common_data = array();
		$objects_data = array();

		foreach($_FILES as $name => $params)
		{
			foreach($params as $key => $value)
			{
				if(is_array($value))
				{
					foreach($value as $idx => $val2)
					{
						$objects_data[$idx]['uploaded_file'][$key] = $val2;
						$objects_data[$idx]['uploaded_file']['upload_name'] = $name;
					}
				}
				else
				{
					$objects_common_data['uploaded_file'][$key] = $value;
					$objects_data['uploaded_file']['upload_name'] = $name;
					$objects_common_data['uploaded_file']['upload_name'] = $name;
				}
			}
		}

		if(empty($_GET['checkboxes_list']))
			$checkboxes_list = array();
		else
			$checkboxes_list = explode(',', $_GET['checkboxes_list']);

		if(!empty($_GET['checkboxes']))
		{
			foreach(explode(',', $_GET['checkboxes']) as $cbn)
			{
				$cbn = str_replace('[]', '', $cbn);
				if(empty($_GET[$cbn]))
					$_GET[$cbn] = 0;
			}

			unset($_GET['checkboxes']);
		}

		foreach($_GET as $key => $value)
		{
			if(is_array($value) && !in_array($key, $checkboxes_list))
			{
				foreach($value as $idx => $val2)
					$objects_data[$idx][$key] = $val2;
			}
			else
				$objects_common_data[$key] = $value;
		}

		$form = $obj;
		if($objects_data)
		{
			$first = true;
			$total = count($objects_data);
			$count = 0;
			foreach($objects_data as $idx => $data)
			{
				$last = (++$count == $total);
				$data = array_merge($data, $objects_common_data);
				$result = bors_form_save_object($data['class_name'], @$data['id'], $data, $first, $last);
				if($result === true || is_object($result))
					$form = $result;
				if(true === $result)
					break;
				$first = false;
			}
		}
		else
			$form = bors_form_save_object($_GET['class_name'], @$_GET['id'], $objects_common_data, true, true);

		if($form === true)
			return true;

		if(!empty($_GET['go']))
		{
			if($_GET['go'] == "newpage")
				return go($form->url(1));

			if($_GET['go'] == "newpage_admin")
				return go($form->admin_url(1));

			if($_GET['go'] == "newpage_edit_parent")
			{
				$p = object_load($form->edit_url(1))->parents();
				return go($p[0]);
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

function bors_form_save_object($class_name, $id, &$data, $first, $last)
{
	if($field = @$data['multiple_check_field'])
		if(empty($data[$field]))
			return;
	
//	if(debug_is_balancer()) { echo "Store object $class_name($id); ".print_d($data, true)."<br/>"; debug_exit('stop0'); }
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

//	if(debug_is_balancer()) { echo "Initial id: {$object->id()}<br />"; bors_exit(); }

	if($first)
	{
		$processed = $object->pre_action($data);
		if($processed === true)
			return true;

		if(!$object->access())
			return bors_message(ec("Не заданы режимы доступа класса ").get_class($object)."; access_engine=".$object->access_engine());

		if(!$object->access()->can_action($data))
			return bors_message(ec("[2] Извините, Вы не можете производить операции с этим ресурсом (class=".get_class($object).", access=".($object->access_engine())."/".get_class($object->access()).", method=can_action)"));

		if(empty($data['subaction']))
			$method = '';
		else
			$method = '_'.addslashes($data['subaction']);

//		echo "? - $object->$method()";
		if(method_exists($object, $method = 'on_action'.$method))
			if($object->$method($data))
				return true;
	}

	if(($ret = $object->check_data($data)))
		return $ret;

	foreach($data as $key => $val)
	{
		if(!$val || !preg_match("!^file_(\w+)_delete_do$!", $key, $m))
			continue;

		$method = "remove_{$m[1]}_file";
		$object->$method($data);
	}

	if($file_data = @$data['uploaded_file'])
	{
		if($file_data['tmp_name'])
			$object->{'upload_'.$file_data['upload_name'].'_file'}($file_data, $data);
		elseif(@$file_data['name'])
			bors_exit(ec("Ошибка загрузки изображения ").$file_data['name']);
	}

//	echo "Set fields for $object: ".print_d($data, true)."<br/>"; exit();
	if($first)
		$object->pre_set($data);

	if(!$object->set_fields($data, true))
		return true;

	if($last)
	{
		$object->set_modify_time(time(), true);
		$object->set_last_editor_id(bors()->user_id(), true);
		$object->post_set($data);
	}

	if(!$object->id() && method_exists($object, 'new_instance'))
	{
		$object->new_instance();
		$object->on_new_instance($data);
	}

	if(!empty($data['bind_to']) && preg_match('!^(\w+)://(\d+)!', $data['bind_to'], $m))
		$object->add_cross($m[1], $m[2], intval(@$data['bind_order']));

	if(!$object->id())
	{
		if($x = $object->empty_id_handler())
			return $x;
		else
			return debug_exit(ec('Пустой id нового объекта ').$object->class_name().ec('. Возможно нужно использовать function replace_on_new_instance() { return true; }'));
	}
	
//	echo "Final id: {$object->id()}<br />";

//	bors()->changed_save();
	
	return $object;
}
