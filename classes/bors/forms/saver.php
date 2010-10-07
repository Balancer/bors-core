<?php

class bors_forms_saver extends base_empty
{
	function save($form_object, $data, $files)
	{
//		echo "On input:"; print_d($data); print_d($files); bors_exit();
		if(empty($data['id']))
		{
			// Создаём новый объект
			echo "Требуется создание нового объекта";
			print_d($data);
			print_d($files);
			bors_exit();
		}
		else
			$object = object_load($data['class_name'], $data['id']);

		if(!empty($data['time_vars']))
			self::parse_time($data);

//		echo "Time vars parsed:"; print_d($data);

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
			require_once('inc/navigation.php');
			switch($data['go'])
			{
				case 'newpage':
					return go($form_object->url(1));
				case 'newpage_admin':
					return go($form_object->admin_url(1));
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

	function parse_time(&$data, $integer = false)
	{
		//TODO: заюзать make_input_time? (funcs/datetime.php)
		foreach(explode(',', $data['time_vars']) as $var)
		{
			// Полный формат данных: YYYY-MM-DD. Если нет - то ниже.
			if(@$data["{$var}_month"] && @$data["{$var}_day"] && @$data["{$var}_year"])
			{
				$data[$var] = strtotime(intval(@$data["{$var}_year"])
					.'-'.intval(@$data["{$var}_month"])
					.'-'.intval(@$data["{$var}_day"])
					.' '.intval(@$data["{$var}_hour"])
					.':'.intval(@$data["{$var}_minute"])
					.':'.intval(@$data["{$var}_seconds"]));
			}
			else // Не полный формат даты, например, 2009-0-0 - пишем как строку.
			{
				if($integer) // или как число вида YYYY0000
				{
					if(@$data["{$var}_year"])
					{
						$d = sprintf('%04d', @$data["{$var}_year"]);
						if(array_key_exists("{$var}_month", $data))
							$d .= sprintf('%02d', $data["{$var}_month"]);
						if(array_key_exists("{$var}_day", $data))
							$d .= sprintf('%02d', $data["{$var}_day"]);

						$data[$var] = intval($d);
					}
					else
						$data[$var] = NULL;
				}
				else
					$data[$var] = intval(@$data["{$var}_year"]).'-'.intval(@$data["{$var}_month"]).'-'.intval(@$data["{$var}_day"]);
			}

			if(empty($data["{$var}_month"]) && empty($data["{$var}_day"]) && empty($data["{$var}_year"]))
				$data[$var] = NULL;

			unset($data["{$var}_hour"], $data["{$var}_minute"], $data["{$var}_second"], $data["{$var}_month"], $data["{$var}_day"], $data["{$var}_year"]);
		}

		unset($data['time_vars']);
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
