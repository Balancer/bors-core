<?php

class bors_forms_saver extends base_empty
{
	function save($form_object, $data, $files)
	{
//		echo "On input:"; print_d($data); print_d($files); bors_exit();
		if(!empty($data['time_vars']))
			bors_lib_time::parse_form($data);

//		echo "Time vars parsed:"; print_d($data);

		if(empty($data['id']))
		{
			// Создаём новый объект
			$object = object_new_instance($data['class_name'], $data);
			$data['id'] = $object->id();
			bors()->changed_save();
		}

		$object = object_load($data['class_name'], $data['id']);

		if(!empty($data['file_vars']))
			self::load_files($object, $data, $files);

		if(($ret = $object->check_data($data)))
			return $ret;

		if(!$object->set_fields($data, true))
			return true;

		if($object->has_changed())
		{
			$object->set_modify_time(time(), true);
			$object->set_last_editor_id(bors()->user_id(), true);
			$object->post_set($data);

			add_session_message(ec('Данные успешно сохранены'), array('type' => 'success'));
		}

		if(!empty($data['go']))
		{
//			var_dump($form_object);
			require_once('inc/navigation.php');
			switch($data['go'])
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
				$data['go'] = str_replace('%OBJECT_ID%', $form_object->id(), $data['go']);
				$data['go'] = str_replace('%OBJECT_URL%', $form_object->url(), $data['go']);
			}

			return go($data['go']);
		}

		return false;
	}

	function load_files($object, &$data, &$files)
	{
		foreach(explode(',', $data['file_vars']) as $f)
		{
			if(!preg_match('/^(\w+)=(\w+)\((\w+)\)$/', $f, $m))
			{
				bors_hidden_log('errors_form', "Unknown file var format: {$f}");
				bors_exit("Unknown file var format: {$f}");
			}

			$file_name = $file_field = $m[1];
			$file_class_name = $m[2];
			$file_id_field = $m[3];

			if(!empty($data['file_'.$file_name.'_delete_do']))
			{
				$old_file = $object->get($file_field);
				if($old_file)
					$old_file->delete();

				$object->set($file_field, NULL, false);
				$object->set($file_id_field, NULL, true);
			}

			$file_data = @$files[$file_name];
			if(!$file_data)
			{
				bors_hidden_log('errors_form', "Empty file data for {$f}");
				bors_exit("Empty file data for {$f}");
			}

/*
				Пусто: Array (
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

			$old_file = $object->get($file_field);
			if($old_file)
			{
				$old_file->set('parent_class_id', NULL, true);
				$old_file->set('parent_object_id', NULL, true);
			}

//			echo "file_name = $file_name, class_name = $file_class_name, id_field = $file_id_field";
			$file_data['upload_dir'] = popval($data, "{$file_field}___upload_dir");
			$file_data['no_subdirs'] = popval($data, "{$file_field}___no_subdirs");
			$file = new $file_class_name(NULL);
			$file->upload($file_data);
			$object->set($file_field, $file, false);
			$object->set($file_id_field, $file->id(), true);
			$file->set('parent_class_id', $object->class_id(), true);
			$file->set('parent_class_name', $object->class_name(), true);
			$file->set('parent_object_id', $object->id(), true);

//			var_dump($data);
//			var_dump($file_data);
//			var_dump($file->data);
		} /* end foreach */
	}
}
