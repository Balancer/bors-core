<?php

class bors_attach extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'bors_attaches'; }
	function table_fields()
	{
		return array(
			'id',
			'title',
			'mime_type',
			'full_file_name',
			'relative_path',
			'original_name',
			'parent_class_name',
			'parent_id',
			'size',
			'modify_time',
			'create_time',
			'owner_id',
			'last_editor_id',
		);
	}

	//TODO: убрать после проверки на существование при повторном аплоаде
	function replace_on_new_instance() { return true; }

	function extension()
	{
		$data = pathinfo($this->original_name());
		return $data['extension'];
	}

	function dirname()
	{
		$data = pathinfo($this->full_file_name());
		return $data['dirname'];
	}

	function basename()
	{
		$data = pathinfo($this->full_file_name());
		return $data['basename'];
	}

	function upload(&$data)
	{
//		var_dump($data);
		if(!file_exists($file = $data['tmp_name']))
		{
			debug_hidden_log('attach-error', 'Upload not existens file '.$file);
			debug_exit("Can't load file {$data['name']}: File not exists<br/>");
		}

		if(!$this->id())
		{
			debug_hidden_log('new-instance-errors', 'empty attach id, try to create new by store');
			$this->new_instance();
		}

		if(!$this->id())
			debug_exit('Error: empty attach id');

		$this->set_original_name($data['name']);

		$dir = popval($data, 'upload_dir');

		if(!empty($data['no_subdirs']))
			$this->set_relative_path(secure_path($dir));
		else
			$this->set_relative_path(secure_path($dir.'/'.sprintf("%03d", intval($this->id()/1000))));

		$original_name = translite_uri_simple(preg_replace('/\.\w+$/', '', $this->original_name()));
		$upload_file_name = $_SERVER['DOCUMENT_ROOT'] . '/' . $this->relative_path() . '/' . defval($data, 'file_name', sprintf('%06d', $this->id()).'-'.$original_name.'.'.$this->extension());
		$this->set_full_file_name($upload_file_name);
		$this->set_mime_type($data['type']);

		if($parent = popval($data, 'parent'))
		{
			$this->set_parent_class_name($parent->extends_class_name());
			$this->set_parent_id($parent->id());
		}

		mkpath($this->dirname(), 0777);
		if(!file_exists($this->dirname()))
			debug_exit("Can't create dir '{$this->dirname()}'<br/>");
		if(!is_writable($this->dirname()))
			debug_exit("Can't write dir '{$this->dirname()}'<br/>");

		if(!move_uploaded_file($file, $this->full_file_name()))
			debug_exit("Can't load image {$data['name']}<br/>");
		@chmod($this->full_file_name(), 0664);

//		$this->recalculate(true);
//		exit();
		return $this;
	}

	function html() { return $this->html_code(); }
	function html_code()
	{
		return "<a href=\"/{$this->relative_path()}/{$this->basename()}\">{$this->original_name()}</a>";
	}
}
