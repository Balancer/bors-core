<?php

class bors_image extends base_object_db
{
	function main_db() { return config('bors_core_db'); }
	function main_table() { return config('images_table', 'bors_images'); }

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
			'full_file_name',
			'full_url',
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

function alt() { return @$this->data['alt']; }
function set_alt($v, $dbup) { return $this->set('alt', $v, $dbup); }
function parent_class_id() { return @$this->data['parent_class_id']; }
function set_parent_class_id($v, $dbup) { return $this->set('parent_class_id', $v, $dbup); }
function parent_object_id() { return @$this->data['parent_object_id']; }
function set_parent_object_id($v, $dbup) { return $this->set('parent_object_id', $v, $dbup); }
function sort_order() { return @$this->data['sort_order']; }
function set_sort_order($v, $dbup) { return $this->set('sort_order', $v, $dbup); }
function author_name() { return @$this->data['author_name']; }
function set_author_name($v, $dbup) { return $this->set('author_name', $v, $dbup); }
function image_type() { return @$this->data['image_type']; }
function set_image_type($v, $dbup) { return $this->set('image_type', $v, $dbup); }
function relative_path() { return @$this->data['relative_path']; }
function set_relative_path($v, $dbup) { return $this->set('relative_path', $v, $dbup); }
function full_file_name() { return @$this->data['full_file_name']; }
function set_full_file_name($v, $dbup) { return $this->set('full_file_name', $v, $dbup); }
function file_name() { return @$this->data['file_name']; }
function set_file_name($v, $dbup) { return $this->set('file_name', $v, $dbup); }
function original_filename() { return @$this->data['original_filename']; }
function set_original_filename($v, $dbup) { return $this->set('original_filename', $v, $dbup); }
function resolution_limit() { return @$this->data['resolution_limit']; }
function set_resolution_limit($v, $dbup) { return $this->set('resolution_limit', $v, $dbup); }
function width() { return @$this->data['width']; }
function set_width($v, $dbup) { return $this->set('width', $v, $dbup); }
function height() { return @$this->data['height']; }
function set_height($v, $dbup) { return $this->set('height', $v, $dbup); }
function size() { return @$this->data['size']; }
function set_size($v, $dbup) { return $this->set('size', $v, $dbup); }
function extension() { return @$this->data['extension']; }
function set_extension($v, $dbup) { return $this->set('extension', $v, $dbup); }
function mime_type() { return @$this->data['mime_type']; }
function set_mime_type($v, $dbup) { return $this->set('mime_type', $v, $dbup); }
function created_from() { return @$this->data['created_from']; }
function set_created_from($v, $dbup) { return $this->set('created_from', $v, $dbup); }
function moderated() { return @$this->data['moderated']; }
function set_moderated($v, $dbup) { return $this->set('moderated', $v, $dbup); }

	function file_name_with_path() { return $this->image_dir().$this->file_name(); }

	// Редкое исключение: возвращаем путь с '/' на конце. (зачем?)
	function image_dir()
	{
		if($ffn = $this->full_file_name())
			return dirname($ffn).'/';

		$rel_path = $this->relative_path().'/';
//		if($rel_path[0] == '/')
//			return $_SERVER['DOCUMENT_ROOT'].$rel_path;

		return secure_path(config('pics_base_dir', $_SERVER['DOCUMENT_ROOT']).'/'.$rel_path);
	}

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
//		debug_hidden_log('recalculate', "$this:\n".print_r($this->data, true));
		$x = @getimagesize($this->url());
		if(!$x)
			$x = @getimagesize($this->file_name_with_path());

		if(!empty($x[0]) && !empty($x['mime']))
		{
			$this->set_width(intval($x[0]), $db_update);
			$this->set_height(intval($x[1]), $db_update);
			$this->set_size(intval(@filesize($this->file_name_with_path())), $db_update);
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

	// Регистрация файла по абсолютному или относительному к DOCUMENTS_ROOT пути.
	// Возвращает объект изображения.
	static function register_file($file, $new_instance = true, $exists_check = true)
	{
		$img = object_new('bors_image');

		$data = url_parse($file);

		if($exists_check && $img2 = objects_first('bors_image', array('full_file_name' => $data['local_path'])))
			return $img2;

		$img->set_original_filename(basename($file), true);
		$img->set_relative_path(dirname($file), true);
		$img->set_full_file_name($data['local_path'], true);
		$img->set_extension(preg_replace('!^.+\.([^\.]+)$!', '$1', $img->original_filename()), true);
		$img->set_file_name($img->original_filename(), true);

		@chmod($img->image_dir(), 0775);
		@chmod($img->file_name_with_path(), 0664);

		$img->recalculate(true);

		if($new_instance)
			$img->new_instance();

		return $img;
	}

	function cross_objects() { return bors_get_cross_objs($this); }

	function delete()
	{
		@unlink($this->file_name_with_path());
		@rmdir($this->image_dir());
		parent::delete();
	}

	function class_title() { return ec('Изображение'); }
	function class_title_vp() { return ec('изображение'); }
	function class_title_rp() { return ec('изображения'); }

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
//		if(debug_is_balancer())
//			debug_hidden_log('1', "file=$file, fex=".file_exists($file));
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
		return "<a href=\"".$this->setdefaultfor_url($object)."\"><img src=\"/_bors/i/notice-16.gif\" width=\"16\" height=\"16\" alt=\"def\" title=\"$title\"/></a>";
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
