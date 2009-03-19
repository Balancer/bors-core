<?php

class bors_image extends base_object_db
{
	function main_table() { return config('images_table', 'bors_images'); }
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

function alt() { return @$this->stb_alt; }
function set_alt($v, $dbup) { return $this->fset('alt', $v, $dbup); }
function parent_class_id() { return @$this->stb_parent_class_id; }
function set_parent_class_id($v, $dbup) { return $this->fset('parent_class_id', $v, $dbup); }
function parent_object_id() { return @$this->stb_parent_object_id; }
function set_parent_object_id($v, $dbup) { return $this->fset('parent_object_id', $v, $dbup); }
function sort_order() { return @$this->stb_sort_order; }
function set_sort_order($v, $dbup) { return $this->fset('sort_order', $v, $dbup); }
function author_name() { return @$this->stb_author_name; }
function set_author_name($v, $dbup) { return $this->fset('author_name', $v, $dbup); }
function image_type() { return @$this->stb_image_type; }
function set_image_type($v, $dbup) { return $this->fset('image_type', $v, $dbup); }
function relative_path() { return @$this->stb_relative_path; }
function set_relative_path($v, $dbup) { return $this->fset('relative_path', $v, $dbup); }
function file_name() { return @$this->stb_file_name; }
function set_file_name($v, $dbup) { return $this->fset('file_name', $v, $dbup); }
function original_filename() { return @$this->stb_original_filename; }
function set_original_filename($v, $dbup) { return $this->fset('original_filename', $v, $dbup); }
function resolution_limit() { return @$this->stb_resolution_limit; }
function set_resolution_limit($v, $dbup) { return $this->fset('resolution_limit', $v, $dbup); }
function width() { return @$this->stb_width; }
function set_width($v, $dbup) { return $this->fset('width', $v, $dbup); }
function height() { return @$this->stb_height; }
function set_height($v, $dbup) { return $this->fset('height', $v, $dbup); }
function size() { return @$this->stb_size; }
function set_size($v, $dbup) { return $this->fset('size', $v, $dbup); }
function extension() { return @$this->stb_extension; }
function set_extension($v, $dbup) { return $this->fset('extension', $v, $dbup); }
function mime_type() { return @$this->stb_mime_type; }
function set_mime_type($v, $dbup) { return $this->fset('mime_type', $v, $dbup); }
function created_from() { return @$this->stb_created_from; }
function set_created_from($v, $dbup) { return $this->fset('created_from', $v, $dbup); }
function moderated() { return @$this->stb_moderated; }
function set_moderated($v, $dbup) { return $this->fset('moderated', $v, $dbup); }


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

	function html_code($append = "")
	{
		return "<img src=\"{$this->url()}\" {$this->wxh()} $append />";
	}

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

		if(!empty($x[0]) && !empty($x['mime']))
		{
			$this->set_width(intval($x[0]), $db_update);
			$this->set_height(intval($x[1]), $db_update);
			$this->set_size(intval(filesize($this->file_name_with_path())), $db_update);
			$this->set_mime_type($x['mime'], $db_update);
//			echo "o=".$this->original_filename();
//			echo "ext=".preg_replace('!^.+\.([^\.]+)$!', '$1', $this->original_filename());
			$this->set_extension(preg_replace('!^.+\.([^\.]+)$!', '$1', $this->original_filename()), $db_update);
			$this->store();
		}
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
