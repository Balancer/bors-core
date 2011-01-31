<?php

class bors_forms_saver extends base_empty
{
	function save($form_object, $data, $files)
	{
		config_set('orm.auto.cache_attr_skip', true);

//		echo "On input {$form_object->debug_title()}:"; print_d($data); print_d($files); bors_exit();
		if(!empty($data['time_vars']))
			bors_lib_time::parse_form($data);

//		echo "Time vars parsed:"; print_d($data);

		$object = NULL;
		if(!empty($data['id']))	// Был передан ID, пытаемся загрузить
			$object = bors_load($data['class_name'], $data['id']);

		if(!$object) // Если не было объекта или нужно создать новый
			$object = object_new($data['class_name']);

		if(!$object) // Так и не получилось создать
			return bors_throw(ec("Не получается создать объект для сохранения ")."{$data['class_name']}({$data['id']})");

		// Проверяем не обрабатывает ли свои сохранения объект сам.
		if($object->pre_action($data) === true)
			return true;

		// Проверяем доступ
		if(!$object->access())
			return bors_message(ec("Не заданы режимы доступа класса ").get_class($object)."; access_engine=".$object->access_engine());

		if(!$object->access()->can_action(@$data['act'], $data))
			return bors_message(ec("[2] Извините, Вы не можете производить операции с этим ресурсом (class=".get_class($object).", access=".($object->access_engine())."/".get_class($object->access()).", method=can_action)"));

		if(empty($data['subaction']))
			$method = '';
		else
			$method = '_'.addslashes($data['subaction']);

		if(method_exists($object, $method = 'on_action'.$method))
			if($object->$method($data))
				return true;

		if($file_vars  = popval($data, 'file_vars'))
			self::load_files($object, $data, $files, $file_vars);

		// Чистим служебные переменные
		$go  = popval($data, 'go');
		$uri = popval($data, 'uri');
		$class_name = popval($data, 'class_name');

		if(($ret = $object->check_data($data)))
			return $ret;

		$object->pre_set($data);

		if(!$object->set_fields($data, true))
			return true;

		// Создаём новый объект, если это требуется
		if(!$object->id())
		{
			$object->new_instance($data);
			$object->on_new_instance($data);
		}

		if(!empty($data['bind_to']) && preg_match('!^(\w+)://(\d+)!', $data['bind_to'], $m))
			$object->add_cross($m[1], $m[2], intval(@$data['bind_order']));

		if($object->has_changed())
		{
			$object->set_modify_time(time(), true);
			$object->set_last_editor_id(bors()->user_id(), true);
			$object->post_set($data);

			add_session_message(ec('Данные успешно сохранены'), array('type' => 'success'));
		}

		if($go)
		{
//			var_dump($form_object);
			require_once('inc/navigation.php');
			switch($go)
			{
				case 'newpage':
					return go($form_object->url(1));
				case 'newpage_admin':
					return go($object->admin_url(1));
				case 'newpage_edit_parent':
				case 'admin_parent':
					if($p = object_load($form_object->admin_url(1)))
					{
						$p = $p->parents();
						return go($p[0]);
					}
					return go($form_objects->url(1));
			}

			if($form_object)
			{
				$go = str_replace('%OBJECT_ID%', $form_object->id(), $data['go']);
				$go = str_replace('%OBJECT_URL%', $form_object->url(), $data['go']);
			}

			return go($go);
		}

		return false;
	}

	function load_files($object, &$data, &$files, $file_vars)
	{
		/**
			Возможные варианты передачи данных о файле.
				Полный: file_vars: image=default_image_class_name(default_image_id)
				Короткий: file_vars: image1,image2...
		*/

//		echo "Сохранение файлов для {$object->debug_title()}"; var_dump($data); var_dump($files); bors_exit();

		foreach(explode(',', $file_vars) as $f)
		{
			$method_name = NULL;
			if(preg_match('/^\w+$/', $f))
			{
				// Это простое указание имени файла. 
				// Обработчик загрузки целиком на совести самого объекта
				// Используются методы upload_<file_name>_file($file_data, $object_data)

				if(!method_exists($object, $method_name = "upload_{$f}_file"))
				{
					debug_hidden_log('errors.forms.files', $msg = "Undefined upload method '$method_name' for {$object}");
					bors_exit($msg);
				}

				$file_name = $f;			// Собственное имя файла
				$file_class_name_field = NULL;		// Имя поля объекта, где хранится класс файла
				$file_id_field = NULL;				// Имя поля объекта, где хранится id файла
			}
			elseif(preg_match('/^(\w+)=(\w+)\((\w+)\)$/', $f, $m))
			{
				$file_name = $file_field = $m[1];	// Собственное имя файла
				$file_class_name_field = $m[2];		// Имя поля объекта, где хранится класс файла
				$file_id_field = $m[3];				// Имя поля объекта, где хранится id файла
			}
			else
			{
				debug_hidden_log('errors.forms.files', $msg = "Unknown file var format: '$f' for {$object}");
				bors_exit($msg);
			}

			// Удаляем старый файл, если есть пометка к его удалению.
			if(!empty($data['file_'.$file_name.'_delete_do']))
			{
				$old_file = $object->get($file_class_name_field);
				if($old_file)
					$old_file->delete();

				if(method_exists($object, $remove_method = "remove_{$file_name}_file"))
					$object->$remove_method($data);

				if($file_class_name_field)
					$object->set($file_class_name_field, NULL, false);
				if($file_id_field)
					$object->set($file_id_field, NULL, true);
			}

			$file_data = $files[$f];
			if(empty($file_data['tmp_name'])) // Файл не загружался. Вызываем после проверки на удаление.
				continue;

			if($method_name) // Обработка вынесена в конец, чтобы корректно обработать удаление выше.
			{
				$object->$method_name($file_data, $data);
				continue;
			}

			$file_data = @$files[$file_name];
			if(!$file_data)
			{
				debug_hidden_log('errors_form', "Empty file data for {$f}");
				bors_exit("Empty file data for {$f}");
			}

/*
				Пустая загрузка выглядит так:
				Array (
				    [image] => Array (
	    		        [name] =>
    	        		[type] =>
		        	    [tmp_name] =>
        		    	[error] => 4
			            [size] => 0
    			    ))
*/
			if(empty($file_data['tmp_name']))
				continue;

			if(is_array($file_data['tmp_name'])) // Загружается массив файлов
			{
/*
				Массив файлов: Array (
		    		[image] => Array (
		            	[name] => Array (
		                    [0] => SYDNEYOP.JPG )
			            [type] => Array (
		                    [0] => image/jpeg )
			            [tmp_name] => Array (
		                    [0] => /tmp/phpYgwIOZ )
			            [error] => Array (
		                    [0] => 0 )
			            [size] => Array (
		                    [0] => 46345 )
		        	))
*/
				print_d($data); print_d($files);
				bors_exit('Загрузка массивов ещё не реализована');
			}
/*
			Одиночные файлы: Array (
			    [image] => Array (
       			    [name] => GOLDGATE.JPG
            		[type] => image/jpeg
	    	        [tmp_name] => /tmp/phpfBfZdS
    			    [error] => 0
        			[size] => 52767
			))
*/

			$old_file = $object->get($file_class_name_field);
			if($old_file)
			{
				$old_file->set('parent_class_id', NULL, true);
				$old_file->set('parent_object_id', NULL, true);
			}

//			echo "file_name = $file_name, class_name = $file_class_name, id_field = $file_id_field";
			$file_data['upload_dir'] = popval($data, "{$file_class_name_field}___upload_dir");
			$file_data['no_subdirs'] = popval($data, "{$file_class_name_field}___no_subdirs");
			$file = new $file_class_name(NULL);
			$file->upload($file_data);
			$object->set($file_class_name_field, $file->extends_class_name(), false);
			$object->set($file_id_field, $file->id(), true);
			$file->set('parent_class_id', $object->class_id(), true);
			$file->set('parent_class_name', $object->extends_class_name(), true);
			$file->set('parent_object_id', $object->id(), true);

//			var_dump($data);
//			var_dump($file_data);
//			var_dump($file->data);
		} /* end foreach */
	}
}
