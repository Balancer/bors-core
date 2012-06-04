<?php

class bors_file extends base_object_db
{
	function class_title() { return ec('Файл'); }
	function _class_title_rp_def() { return ec('файла'); }
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
			'full_file_name' => array('title' => ec('Файл'), 'type' => 'file_name'),
		);
	}

	function url()
	{
		return '/'.$this->relative_path().'/'.basename($this->full_file_name());
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'parent_link' => 'parent_class_name(parent_id)',
		));
	}

	function bors_url()
	{
		return '/'.$this->relative_path().'/'.basename($this->full_file_name());
	}

	function size_smart()
	{
		$size = $this->size();

		if($size < 1024) // 1 ..55 ..999 ... 1000
			return $size.ec(' байт');

		if($size < 1024*10) // 1,2 .. 1,5 .. 9,8
			return round($size/1024, 1).ec(' кб');

		if($size < 1024*1024)
			return round($size/1024).ec(' кб');

		if($size < 1024*1024*10) // 2,5 Мб
			return round($size/1024/1024, 1).ec(' Мб');

		return round($size/1024/1024).ec(' Мб');
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
		$class_name = self::called_class_name(NULL, popval($data, 'class_name'));

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

		@chmod($file, 0777);
		@chmod(dirname($file), 0666);

		return bors_new($class_name, $data);
	}

	static function upload($file_data)
	{
		$class_name = self::called_class_name(NULL, popval($file_data, 'class_name'));

		if(!file_exists($tmp_file = $file_data['tmp_name']))
		{
			debug_hidden_log('file-error', 'Upload not existens file '.$tmp_file);
			bors_throw("Can't load file {$file_data['name']}: File not exists<br/>");
		}

		$original_filename = $file_data['name'];
		$mime_type = $file_data['type'];

		$base_dir   = config('file_upload.base_dir', bors()->server()->document_root());
		$upload_dir = popval($file_data, 'upload_dir', 'uploads/common');

		$ext = preg_replace('!^.+\.([^\.]+)$!', '$1', $original_filename);

		$translated_name = translite_uri_simple(preg_replace('/\.\w+$/', '', $original_filename));

		$file = bors_new($class_name, array(
			'original_filename' => $original_filename,
			'mime_type' => $mime_type,
			'size' => $file_data['size'],
			'extension' => $ext,
		));

		if(config('file_upload.skip_subdirs') || !empty($file_data['no_subdirs']))
			$relative_path = secure_path($upload_dir);
		else
			$relative_path = secure_path($upload_dir.'/'.date('Ym').sprintf("_%03d", intval($file->id()/1000)));

		$file->set_relative_path($relative_path);

		$dir = $base_dir.'/'.$relative_path;

		if(!preg_match('/^\w+$/', $ext))
			$ext = array_pop(explode('/', $mime_type));

		$upload_file_name = $dir.'/'.defval($file_data, 'file_name', sprintf('%06d', $file->id()).'-'.$translated_name.'.'.$ext);
		mkpath($dir, 0777);
		if(!is_dir($dir))
			bors_throw("Can't create dir '{$dir}' for upload file {$file_data['name']}");
		if(!is_writable($dir))
			bors_throw("Can't write uploaded file {$file_data['name']} to dir '{$dir}'");
		if(!move_uploaded_file($tmp_file, $upload_file_name))
			bors_throw("Can't upload image {$file_data['name']} as {$upload_file_name}");

		@chmod($upload_file_name, 0666);
		$file->set_full_file_name($upload_file_name);
		$file->store();
		return $file;
	}

	function on_delete_pre()
	{
		$file = $this->full_file_name();

		@unlink($file);
		$dir = dirname($file);

		do
		{
			@rmdir($dir);
		} while(!is_dir($dir) && ($dir = dirname($dir)) && $dir != '/');

		return parent::on_delete_pre();
	}

	function title_smart()
	{
		if($this->title(true))
			return $this->title();

		if($this->original_filename())
			return $this->original_filename();

		if($this->description())
			return $this->description();

		return basename($this->full_file_name());
	}

	function html()
	{
		return ec('скачать файл ')."<a href=\"{$this->url()}\">{$this->title_smart()} [{$this->size_smart()}]</a>";
	}

	function upload_file($file_data, $object_data)
	{
		@unlink($this->full_file_name());
		if(file_exists($this->full_file_name()))
			bors_throw(ec('Не могу удалить старый файл ').$this->full_file_name());

		if(!move_uploaded_file($file_data['tmp_name'], $this->full_file_name()))
			bors_throw("Can't upload image {$file_data['name']} as {$this->full_file_name()}");

		$this->set_original_filename($file_data['name']);
		$this->set_size($file_data['size']);
		$this->set_mime_type($file_data['type']);
	}

	function skip_auto_admin_new() { return true; }

	function pre_show()
	{
		return go($this->bors_url(), true);
	}

	function has_no_links($skip_object = NULL)
	{
		if(($p = $this->parent_link()) && !bors_eq($p, $skip_object))
			return false;

		foreach($this->cross_objs() as $p)
			if(!bors_eq($p, $skip_object))
				return false;

		return true;
	}

	// Заглушки для legacy
	function mime() { return $this->mime_type(); }
}
