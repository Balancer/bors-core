<?php

class bors_file extends base_object_db
{
	function class_title() { return ec('Файл'); }
	function class_title_rp() { return ec('файла'); }
	function class_title_vp() { return ec('файл'); }
	function class_title_m() { return ec('файлы'); }
	function class_title_tpm() { return ec('файлами'); }

	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'files'; }
	function table_fields()
	{
		return array(
			'full_path' => array('is_editable' => false),
			'title' => array('title' => ec('Название')),
			'description' => array('title' => ec('Описание'), 'type' => 'bbcode'),
			'relative_path' => array('is_editable' => false),
			'mime_type' => array('is_editable' => false),
			'extension' => array('is_editable' => false),
			'parent_class_name' => array('is_editable' => false),
			'parent_id' => array('is_editable' => false),
			'sort_order' => array('title' => ec('Порядок сортировки')),
			'full_url' => array('is_editable' => false),
			'original_filename' => array('is_editable' => false),
			'size' => array('is_editable' => false),
			'owner_id' => array('is_editable' => false),
			'last_editor_id' => array('is_editable' => false),
			'full_file_name' => array('title' => ec('Файл'), 'type' => 'file'),
		);
	}

	// Жизненно необходимо, так как при создании новой записи в БД ещё не залитый файл
	// имеет пустое поле full_file_name, которое UNIQUE. При сбоях пустая запись
	// не должна нам помешать залить новый файл.
	function replace_on_new_instance() { return true; }

	/*	Возможные аргументы:
			'class_name' — имя класса для регистрации
			'base_dir' — полное имя каталога, в котором находятся файлы
	*/

	static function register($file, $data = array())
	{
		$class_name = popval($data, 'class_name', __CLASS__);

		// Если файл с таким именем уже зарегистрирован — возвращаемся.
		if($prev = bors_find_first($class_name, array(
			'full_file_name' => $file,
		)))
			return $prev;

		if($base_dir = popval($data, 'base_dir'))
		{
			if(preg_match('/^'.preg_quote($base_dir).'(.+)$/', $file, $m))
				$data['relative_path'] = dirname($m[1]);
		}

		if($base_url = popval($data, 'base_url'))
			$data['full_url'] = $base_url.'/'.$data['relative_path'].'/'.basename($file);

		$data['full_file_name'] = $file;
		$data['extension'] = preg_replace('!^.+\.([^\.]+)$!', '$1', $file);
		$data['size'] = filesize($file);

		@chmod($file, 0775);
		@chmod(dirname($file), 0664);

		return bors_new($class_name, $data);
	}

	function upload_file($file_data, &$object_data)
	{
		if(!file_exists($tmp_file = $file_data['tmp_name']))
		{
			debug_hidden_log('file-error', 'Upload not existens file '.$tmp_file);
			bors_throw("Can't load file {$file_data['name']}: File not exists<br/>");
		}

		$original_filename = $this->set_original_filename($file_data['name'], true);
		$mime_type = $this->set_mime_type($file_data['type'], true);
		$this->set_size($file_data['size'], true);

		if(!$this->id())
			bors_throw('File upload error: empty file id');

		$base_dir   = config('file_upload.base_dir', bors()->server()->document_root().'/uploads');
		$upload_dir = popval($file_data, 'upload_dir', 'common');

		if(config('file_upload.skip_subdirs') || !empty($file_data['no_subdirs']))
			$relative_path = $this->set_relative_path(secure_path($upload_dir), true);
		else
			$relative_path = $this->set_relative_path(secure_path($upload_dir.'/'.date('Ym').sprintf("_%03d", intval($this->id()/1000))), true);

		$dir = $base_dir.'/'.$relative_path;

		$translated_name = translite_uri_simple(preg_replace('/\.\w+$/', '', $original_filename));
		$ext = $this->set_extension(preg_replace('!^.+\.([^\.]+)$!', '$1', $original_filename), true);

		if(!preg_match('/^\w+$/', $ext))
			$ext = array_pop(explode('/', $mime_type));

		$upload_file_name = $dir.'/'.defval($file_data, 'file_name', sprintf('%06d', $this->id()).'-'.$translated_name.'.'.$ext);

		mkpath($dir, 0777);
		if(!is_dir($dir))
			bors_throw("Can't create dir '{$dir}' for upload file {$file_data['name']}");
		if(!is_writable($dir))
			bors_throw("Can't write uploaded file {$file_data['name']} to dir '{$dir}'");
		if(!move_uploaded_file($tmp_file, $upload_file_name))
			bors_throw("Can't upload image {$file_data['name']} as {$upload_file_name}");

		@chmod($upload_file_name, 0664);

		$this->set_full_file_name($upload_file_name, true);

		$this->store();

		return $this;
	}
}
