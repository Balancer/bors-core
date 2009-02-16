<?php

class bors_image extends base_object_db
{
	function main_table_storage() { return 'bors_images'; }
	function main_db_storage() { return config('bors_core_db'); }

	function main_table_fields()
	{
		return array(
			'id',
			'title',
			'alt',
			'description',
			'parent_class_id',
			'parent_object_id',
			'sort_order',
			'author_name',
			'image_type',
			'create_time',
			'modify_time',
			'relative_path',
			'file_name',
			'original_filename',
			'resolution_limit',
			'width',
			'height',
			'size',
			'extension',
			'mime_type',
			'created_from',
			'moderated',
		);
	}

	function file_name_with_path() { return $this->image_dir().$this->file_name(); }

	function image_dir() { return secure_path(config('pics_base_dir', $_SERVER['DOCUMENT_ROOT']).'/'.$this->relative_path().'/'); }

	function url() { return secure_path(config('pics_base_url').'/'.$this->relative_path().'/'.$this->file_name()); }

	function wxh()
	{
		if($this->width() == 0 || $this->height() == 0)
			$this->recalculate(true);

		$w = $this->width() ? "width=\"{$this->width()}\"" : "";
		$h = $this->height() ? "height=\"{$this->height()}\"" : "";

		return  "{$h} {$w} alt=\"[image]\" title=\"".htmlspecialchars($this->alt_or_description())."\"";
	}

	function html_code($append = "") { return "<img src=\"{$this->url()}\" {$this->wxh()} $append title=\"{$this->id()}\" />"; }

	function thumbnail($geometry) { return object_load('bors_image_thumb', $this->id().','.$geometry); }

	function init()
	{
		parent::init();

		if(!$this->width())
			$this->recalculate(true);
	}

	function recalculate($db_update)
	{
		$x = @getimagesize($this->url());
		if(!$x)
			$x = @getimagesize($this->file_name_with_path());
		$this->set_width(intval(@$x[0]), $db_update);
		$this->set_height(intval(@$x[1]), $db_update);
		$this->set_size(intval(@filesize($this->file_name_with_path())), $db_update);
		$this->set_mime_type(@$x['mime'], $db_update);
//		echo "o=".$this->original_filename();
		$this->set_extension(preg_replace('!^.+\.([^\.]+)$!', '$1', $this->original_filename()), $db_update);
		$this->store();
	}

	function admin_url() { return config('admin_host_url').'/images/'.($this->id() ? $this->id() : '%OBJECT_ID%').'/'; }

	function upload($data, $dir)
	{
		if(!file_exists($file = $data['tmp_name']))
		{
			debug_hidden_log('image-error', 'Upload not existens file '.$file);
			debug_exit("Can't load image {$data['name']}: File not exists<br/>");
		}

		if(!($x = @getimagesize($file)))
		{
			debug_hidden_log('image-error', 'Can not get image sizes for '.$file);
			debug_exit("Can't load image {$data['name']}: Incorrect image<br/>");
		}

		if(!$x[0] || !$x[1] || !preg_match('/^image/', $x['mime']))
		{
			debug_hidden_log('image-error', 'Got wrong image sizes for '.$file);
			debug_exit("Can't load image {$data['name']}: Wrong file format<br/>");
		}

		if(!$this->id())
		{
			debug_hidden_log('new-instance-errors', 'empty image id, try to create new by store');
			$this->new_instance();
		}

		if(!$this->id())
			debug_exit('Error: empty image id');

		$this->set_original_filename($data['name'], true);

		$this->set_relative_path(secure_path($dir.'/'.$this->id()%100), true);
		$this->set_extension(preg_replace('!^.+\.([^\.]+)$!', '$1', $this->original_filename()), true);
		$this->set_file_name($this->id().'.'.$this->extension(), true);

		mkpath($this->image_dir(), 0777);
		if(!move_uploaded_file($file, $this->file_name_with_path()))
			debug_exit("Can't load image {$data['name']}<br/>");
		@chmod($this->file_name_with_path(), 0664);

		$this->recalculate(true);

		return $this;
	}

	function register_file($path)
	{
/*		if(!file_exists($path))
		{
			$data = url_parse($path);
			$path = $data['local_path'];
		}
*/
		$this->set_original_filename(basename($path), true);

		$this->set_relative_path(dirname($path), true);
		$this->set_extension(preg_replace('!^.+\.([^\.]+)$!', '$1', $this->original_filename()), true);
		$this->set_file_name($this->original_filename(), true);

		@chmod($this->image_dir(), 0775);
		@chmod($this->file_name_with_path(), 0664);

		$this->recalculate(true);

		return $this;
	}

	function cross_objects() { return bors_get_cross_objs($this); }

	function delete()
	{
		@unlink($this->file_name_with_path());
		@rmdir($this->image_dir());
		parent::delete();
	}

	function class_title() { return ec('изображение'); }

	function description_or_title()
	{
		if($desc = $this->description())
			return $desc;

		if($title = $this->title())
			return $title;

		return ec('[без имени]');
	}

	function alt_or_description()
	{
		if($alt = $this->alt())
			return $alt;

		if($desc = $this->description())
			return $desc;

		return '';
	}

	function pre_show()
	{
		$file = $this->file_name_with_path();
		if(!file_exists($file))
			$file = $_SERVER['DOCUMENT_ROOT'] . $file;

		if(!file_exists($file))
			return false;

		@header('Content-type: ' . $this->mime_type());
		@header('Content-Length: ' . filesize($file));
		echo file_get_contents($file);
		return true;
	}

	function parent_object() { return object_load($this->parent_class_id(), $this->parent_object_id()); }

	function can_cached() { return false; }

	function setdefaultfor_url($obj)  { return "/admin/tools/set-default/?object={$obj->internal_uri()}&image={$this->internal_uri()}"; }
	function imaged_set_default_url($object, $title = NULL)
	{
		if($title === NULL)
			$title = ec('Сделать изображением по умолчанию');
		return "<a href=\"".$this->setdefaultfor_url($object)."\"><img src=\"/bors-shared/images/notice-16.gif\" width=\"16\" height=\"16\" alt=\"def\" title=\"$title\"/></a>";
	}

//	function replace_on_new_instance() { return $this->id() == 0; }

	private $_parents = false;
	function parents()
	{
		if($this->_parents !== false)
			return $this->_parents;

		$this->_parents = $this->cross_objs();
		if($p = object_load($this->parent_class_id(), $this->parent_object_id()))
			$this->_parents[] = $p;

		return $this->_parents;
	}

	function editor_fields_list()
	{
		return array(
			ec('Заголовок:') => 'title',
			ec('Описание:') => 'description|textarea',
			ec('Изображение:') => 'id|image=468x468',
		);
	}

	function access_engine() { return config('access_public_class', 'access_base'); }
}
