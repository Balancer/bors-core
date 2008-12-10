<?php

//TODO: в старом коде сделать замены:
//		preAction -> pre_action
//		onAction* -> on_action*

//передать также: 'subaction'

function bors_form_save(&$obj)
{
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
		
		foreach($_GET as $key => $value)
		{
			if(is_array($value))
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
				if(true === ($form = ($result = bors_form_save_object($data['class_name'], @$data['id'], $data, $first, $last))))
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
	
//	echo "Store object $class_name($id); ".print_d($data, true)."<br/>"; debug_exit('stop0');
	if($id)
		$object = object_load($class_name, $id);
	else
		$object = object_new($class_name);

	if(!$object)
		return bors_message(ec("Не могу сохранить объект ")."{$class_name}({$id})");

//	echo "Initial id: {$object->id()}<br />"; bors_exit();

	if($first)
	{
		$processed = $object->pre_action($data);
		if($processed === true)
			return true;

		if(!$object->access())
			return bors_message(ec("Не заданы режимы доступа класса ").get_class($object)."; access_engine=".$object->access_engine());

		if(!$object->access()->can_action())
			return bors_message(ec("[2] Извините, Вы не можете производить операции с этим ресурсом (class=".get_class($object).", access=".($object->access_engine())."/".get_class($object->access()).", method=can_action)"));

		if(empty($data['subaction']))
			$method = '';
		else
			$method = '_'.addslashes($data['subaction']);

		if(method_exists($object, $method = 'on_action'.$method))
			if($object->$method($data))
				return true;
	}
	
	if($object->check_data($data) === true)
		return true;

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

//	echo "Set fields for $object: ".print_d($data, true)."<br/>"; set_loglevel(10,0);
	if($first)
		$object->pre_set($data);

	if(!$object->set_fields($data, true))
		return true;
		
	if($last)
	{
		$object->set_modify_time(time(), true);
		$object->post_set($data);
	}
	
	if(!$object->id() && method_exists($object, 'new_instance'))
		$object->new_instance();

	if(!empty($data['bind_to']) && preg_match('!^(\w+)://(\d+)!', $data['bind_to'], $m))
		$object->add_cross($m[1], $m[2], intval(@$data['bind_order']));

	if(!$object->id())
		return debug_exit('Empty id for '.$object->class_name());

//	echo "Final id: {$object->id()}<br />";

//	bors()->changed_save();
	
	return $object;
}
