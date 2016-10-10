<?php

use B2\Cfg;

//передать также: 'subaction'

function bors_form_save($obj)
{
	if(($post_js = @$_GET['javascript_post_append']))
		session_array_append('javascript_post_append', $post_js);

	if(!empty($_GET['act']))
	{
		if($_GET['act'] == 'skip_all')
			return false;

		if(!empty($_GET['object']))
			$obj = bors_load_uri($_GET['object']);

		if(method_exists($obj, 'action_target'))
			$obj = $obj->action_target();

		if(!$obj->access())
			return bors_message(ec("Не заданы режимы доступа класса ").get_class($obj)."; access_engine=".$obj->access_engine());

		if(!$obj->access()->can_action($_GET['act'], $_GET))
		{
//			jquery::load();

			$sorry = _("Извините, у Вас недостаточный уровень доступа для операций с этим ресурсом");
			$info  = _("Служебная информация");

			$message = "<div class=\"alert alert-error\">{$sorry} ({$obj->titled_link()} -> {$obj->access()} -> can_action())</div>
<div class=\"accordion\" id=\"sysinfoacc\">
	<div class=\"accordion-group\">
		<div class=\"accordion-heading\">
			<a class=\"accordion-toggle\" data-toggle=\"collapse\" data-parent=\"#sysinfoacc\" href=\"#sysinfodata\">
				$info
			</a>
		</div>
		<div id=\"sysinfodata\" class=\"accordion-body collapse\">
			<div class=\"accordion-inner\">
				object class = ".get_class($obj).",<br/>
				access class = ".get_class($obj->access()).",<br/>
				access method = can_action(".$_GET['act'].");<br/>
<pre>".bors_debug::trace(0, false)."
			</pre></div>
		</div>
	</div>
</div>
";

			echo twitter_bootstrap::raw_message(array(
				'this' => bors_load('bors_pages_fake', array(
					'title' => ec('Ошибка прав доступа'),
					'body' => $message,
				)),
			));

			return true;
		}

		if(method_exists($obj, $method = "on_action_{$_GET['act']}"))
		{
			$result = $obj->$method($_GET);
			if($result === true)
				return true;
		}
	}

	if(method_exists($obj, 'on_action'))
	{
		//TODO: придумать, как унифицировать с классом-сейвером
		$form = bors_load(@$_GET['form_class_name'], @$_GET['form_object_id']);
		if(!empty($_GET['saver_prepare_classes']))
		{
			foreach(explode(',', $_GET['saver_prepare_classes']) as $cn)
				if(true === call_user_func(array($cn, 'saver_prepare'), $_GET))
					return true;
		}

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

	$class_name = @$_GET['class_name'];
	$form_class_name = @$_GET['form_class_name'];
	if(
			($class_name && $class_name != 'NULL')
		||	($form_class_name && $form_class_name != 'NULL'))
	{
		if($class_name)
			$tmp = new $class_name(NULL);
		elseif($form_class_name)
			$tmp = new $form_class_name(NULL);
		else
			$tmp = NULL;

		if($form_saver_class = object_property($tmp, 'form_saver_class'))
			return object_load($form_saver_class)->save($obj, $_GET, $_FILES);
		elseif($form_saver_class = Cfg::get('form_saver.class_name'))
			return object_load($form_saver_class)->save($obj, $_GET, $_FILES);

		$objects_common_data = array();
		$objects_data = array();

		$files_as_array = true;
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
					$files_as_array = false;
					$objects_common_data['uploaded_file'][$key] = $value;
					$objects_common_data['uploaded_file']['upload_name'] = $name;
				}
			}

			if(!$files_as_array)
				$objects_data[]['uploaded_file'] = $objects_common_data['uploaded_file'];
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
				$data = array_merge($objects_common_data, $data);
				$result = bors_form_save_object($data['class_name'], @$data['object_id'], $data, $first, $last);
				if($result === true || is_object($result))
					$form = $result;
				if(true === $result)
					break;
				$first = false;
			}
		}
		else
			$form = bors_form_save_object($_GET['class_name'], @$_GET['object_id'], $objects_common_data, true, true);

		if($form === true)
			return true;

		if($form->has_changed())
		{
			if($post_message = pop_session_var('post_message'))
			{
				$link_url = bors_form_parse_vars(pop_session_var('post_message_link_url'), $form);
				$post_message = bors_form_parse_vars($post_message, $form);
				return bors_message($post_message, array(
					'title' => ec('Данные сохранены'),
					'link_text' => pop_session_var('post_message_link_text'),
					'link_url' => $link_url,
					'data' => array(
						'target_admin_url' => $form->admin_url(1),
					)
				));
			}

			if(!empty($_GET['go_on_update']))
				$_GET['go'] = $_GET['go_on_update'];
		}

		if(!empty($_GET['go']))
		{
			if($_GET['go'] == "newpage")
				return go($form->url());

			if($_GET['go'] == "newpage_admin")
				return go($form->admin_url());

			if($_GET['go'] == "newpage_edit_parent" || $_GET['go'] == "admin_parent")
			{
				$p = object_load($form->admin_url());
				if($p)
				{
					$p = $p->parents();
					return go($p[0]);
				}

				return go($form->url());
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

	if($first)
	{
		$processed = $object->pre_action($data);
		if($processed === true)
			return true;

		if(!$object->access())
			return bors_message(ec("Не заданы режимы доступа класса ").get_class($object)."; access_engine=".$object->access_engine());

		if(!$object->access()->can_action(@$data['act'], $data))
			return bors_message(ec("[2fs] Извините, Вы не можете производить операции с этим ресурсом<br/><small>(class=".get_class($object).", access=".($object->access_engine())."/".get_class($object->access()).", method=can_action)</small>"));

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

//	echo "Set fields for $object: ".print_d($data, true).", last=$last{}<br/>"; exit();
	if($first)
		$object->pre_set($data);

	//TODO: костыль для bors_admin_image_append
	// Для исправной работы старых кривых ссылоки вида http://balancer.ru/tools/search/result/?q=%D1%82%D1%8D%D0%BC2%D1%83&w=a&s=r&class_name=bors_tools_search
	// Проверка: bors_tools_search
	if(method_exists($object, 'skip_save') && $object->skip_save())
	{
		if(!$object->set_fields($data, true))
			return true;
	}
	else
	{
		if(!$object->set_fields($data, true))//array_intersect_key($data, bors_lib_orm::all_field_names($object, false)), true))
			return true;
	}

	if($last && $object->has_changed())
	{
		$object->set_modify_time(time());
		$object->set_last_editor_id(bors()->user_id());
		$object->set_last_editor_ip(bors()->client()->ip());
		$object->set_last_editor_ua(bors()->client()->agent());

		if($object->id()) // post_set() вызывается только для уже существующих объектов.
			$object->post_set($data);
	}

	if(!$object->id() && method_exists($object, 'new_instance'))
	{
		$object->new_instance($data);
		$object->on_new_instance($data);
		$was_new = true;
	}
	else
		$was_new = false;

	if(method_exists($object, 'post_save'))
		$object->post_save($data);

	$_GET['go'] = defval($data, 'go', @$_GET['go']);
	$_GET['go_on_update'] = defval($data, 'go_on_update', @$_GET['go_on_update']);

	if(!empty($data['bind_to']) && preg_match('!^(\w+)://(\d+)!', $data['bind_to'], $m))
		$object->add_cross($m[1], $m[2], intval(@$data['bind_order']));

	if($data['form_class_name'] != $data['class_name'])
	{
		if(!$object->id() && !(method_exists($object, 'skip_save') && $object->skip_save())) //TODO: костыль для bors_admin_image_append
		{
			if($x = $object->empty_id_handler())
				return $x;
			else
				return bors_throw(ec('Пустой id нового объекта ').$object->class_name().ec('. Возможно нужно использовать function replace_on_new_instance() { return true; }'));
		}
	}

//	echo "Final id: {$object->id()}<br />";

//	bors()->changed_save();

	if($object->has_changed())
		add_session_message(ec('Данные успешно сохранены')/*.print_r(bors()->changed_objects(), true)*/, array('type' => 'success'));

//	echo session_message(array('type' => 'success'));
//	exit( '?');

	if($was_new)
		$object->b2_post_new($data);
	else
		$object->b2_post_update($data);

	return $object;
}

function bors_form_errors($data, $conditions = array())
{
	set_session_form_data($data);

	foreach($conditions as $error_condition => $fail_message)
	{
		if(is_numeric($error_condition) && !is_array($fail_message))
		{
			$error_condition = $fail_message;
			$fail_message = NULL;
		}

		$error_cond = @trim($error_condition);

		if(is_callable($error_condition))
		{
			$error_cond = call_user_func($callback, $data);
		}
		elseif(is_array($fail_message))
		{
			$fields = array($fail_message[0]);
			$error_cond = $fail_message[1];
			$fail_message = $fail_message[2];
		}
		elseif(preg_match('/^!(\w+)$/', $error_cond, $m))
		{
			$fields     = array($m[1]);
			$error_cond = empty($data[$m[1]]);
			if(!$fail_message && !empty($data['class_name']))
			{
//				var_dump($data);
				$f = bors_lib_orm::parse_property($data['class_name'], $m[1]);
//				var_dump($f); exit();
				$fail_message = ec('Не заполнено поле «').defval($f, 'title', $m[1]).ec('»');
			}
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

function bors_form_parse_vars($text, $object)
{
	$text = str_replace('{$target_admin_url}', $object->admin()->url(), $text);
	$text = str_replace('$target_admin_url', $object->admin()->url(), $text);
	return $text;
}
