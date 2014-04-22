<?php

class bors_image extends bors_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }

	function db_name() { return config('bors_core_db'); }
	function table_name() { return config('images_table', 'bors_images'); }

	function ignore_on_new_instance() { return true; }

	function table_fields()
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
			'hash_y',
			'hash_r',
			'hash_g',
			'hash_b',
		);
	}

function alt() { return @$this->data['alt']; }
function set_alt($v, $dbup=true) { return $this->set('alt', $v, $dbup); }
function parent_class_id() { return @$this->data['parent_class_id']; }
function set_parent_class_id($v, $dbup=true) { return $this->set('parent_class_id', $v, $dbup); }
function parent_object_id() { return @$this->data['parent_object_id']; }
function set_parent_object_id($v, $dbup=true) { return $this->set('parent_object_id', $v, $dbup); }
function sort_order() { return @$this->data['sort_order']; }
function set_sort_order($v, $dbup=true) { return $this->set('sort_order', $v, $dbup); }
function author_name() { return @$this->data['author_name']; }
function set_author_name($v, $dbup=true) { return $this->set('author_name', $v, $dbup); }
function image_type() { return @$this->data['image_type']; }
function set_image_type($v, $dbup=true) { return $this->set('image_type', $v, $dbup); }
function relative_path() { return @$this->data['relative_path']; }
function set_relative_path($v, $dbup=true) { return $this->set('relative_path', $v, $dbup); }
function full_file_name() { return @$this->data['full_file_name']; }
function set_full_file_name($v, $dbup=true) { return $this->set('full_file_name', $v, $dbup); }
function file_name() { return @$this->data['file_name']; }
function set_file_name($v, $dbup=true) { return $this->set('file_name', $v, $dbup); }
function original_filename() { return @$this->data['original_filename']; }
function set_original_filename($v, $dbup=true) { return $this->set('original_filename', $v, $dbup); }
function resolution_limit() { return @$this->data['resolution_limit']; }
function set_resolution_limit($v, $dbup=true) { return $this->set('resolution_limit', $v, $dbup); }
function width() { return @$this->data['width']; }
function set_width($v, $dbup=true) { return $this->set('width', $v, $dbup); }
function height() { return @$this->data['height']; }
function set_height($v, $dbup=true) { return $this->set('height', $v, $dbup); }
function size() { return @$this->data['size']; }
function set_size($v, $dbup=true) { return $this->set('size', $v, $dbup); }
function extension() { return @$this->data['extension']; }
function set_extension($v, $dbup=true) { return $this->set('extension', $v, $dbup); }
function mime_type() { return @$this->data['mime_type']; }
function set_mime_type($v, $dbup=true) { return $this->set('mime_type', $v, $dbup); }
function created_from() { return @$this->data['created_from']; }
function set_created_from($v, $dbup=true) { return $this->set('created_from', $v, $dbup); }
function moderated() { return @$this->data['moderated']; }
function set_moderated($v, $dbup=true) { return $this->set('moderated', $v, $dbup); }

	function auto_targets()
	{
		return array_merge(parent::auto_targets(array(
			'parent_object' => 'parent_class_id(parent_object_id)',
		)));
	}

	function file_name_with_path()
	{
		return $this->image_dir().$this->file_name();
	}

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

	function url_ex($page) { return $this->url(); }
	function url()
	{
		if($u = $this->get('full_url'))
			return $u;

		$fn = $this->file_name();
		if(preg_match('/\.$/', $fn))
			$fn .= 'jpg';

		return secure_path(config('pics_base_url').'/'.$this->relative_path().'/'.$fn);
	}

	function wxh($use_alt_title = true)
	{
		if($this->width() == 0 || $this->height() == 0)
			$this->recalculate(config('cache_database') ? true : false);

		$w = $this->width() ? "width=\"{$this->width()}\"" : "";
		$h = $this->height() ? "height=\"{$this->height()}\"" : "";

		if($use_alt_title)
			$alt = "alt=\"[image]\" title=\"".htmlspecialchars($this->alt_or_description())."\"";
		else
			$alt = "alt=\"\"";

		return  "{$h} {$w} $alt";
	}

	function html($args = array()) { return $this->html_code(@$args['append']); }
	function html_code($append = "", $use_alt_title=true)
	{
		return "<img src=\"{$this->url()}\" ".$this->wxh($use_alt_title)." $append />";
	}

	function thumbnail_class() { return 'bors_image_thumb'; }
	function thumbnail($geometry)
	{
		return bors_load_ex($this->thumbnail_class(), $this->id().','.$geometry, array(
			'image_class_name' => $this->class_name(),
		));
	}

	function data_load()
	{
		parent::data_load();
		if(!$this->width())
			$this->recalculate(true);
	}

	function recalculate()
	{
		if(!$this->file_name())
		{
			//TODO: логи забиваются страшно. Непонятно…
//			debug_hidden_log("image-data-error", "empty file_name() on recalculate image url='{$this->url()}', this={$this}, data=".print_r($this->data, true));
			return;
		}

		debug_timing_start('image_recalculate');
		$start = microtime(true);
		$x = @getimagesize($this->url());
		if(!$x)
			$x = @getimagesize($this->file_name_with_path());

		if(!empty($x[0]) && !empty($x['mime']))
		{
			$this->set_width(intval($x[0]));
			$this->set_height(intval($x[1]));
			$this->set_size(intval(@filesize($this->file_name_with_path())));
			$this->set_mime_type($x['mime']);
			$this->set_extension(preg_replace('!^.+\.([^\.]+)$!', '$1', $this->original_filename()));
			try
			{
				$this->store();
			}
			catch(Exception $e) { }
		}
		debug_timing_stop('image_recalculate');
		if(($dura = (microtime(true) - $start)) > 0.5)
			debug_hidden_log("recalculate", "time = $dura, url = {$this->url()}, this={$this->debug_title()}, data=".print_r($this->data, true));
	}

	function upload($data, $dir = NULL)
	{
		if(!file_exists($file = $data['tmp_name']))
		{
			debug_hidden_log('image-error', 'Upload not existens file '.$file);
			debug_exit("Can't load image {$data['name']}: Uploaded tmp file not exists<br/>");
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
//			debug_hidden_log('new-instance-errors', 'empty image id, try to create new by store');
			$this->new_instance();
		}

		if(!$this->id())
			debug_exit('Error: empty image id');

		$this->set_original_filename($data['name']);

		if(is_null($dir))
			$dir = popval($data, 'upload_dir');

		if(config('image_upload_skip_subdirs') || !empty($data['no_subdirs']))
			$this->set_relative_path(secure_path($dir));
		else
			$this->set_relative_path(secure_path($dir.'/'.sprintf("%03d", intval($this->id()/1000))));

		$data = @getimagesize($file);
		switch($data['mime'])
		{
			case 'image/jpeg':
				$ext = 'jpg';
				break;
			case 'image/png':
				$ext = 'png';
				break;
			case 'image/gif':
				$ext = 'gif';
				break;
			default:
				debug_hidden_log('image-upload-error', "Unknown mime: {$data['mime']}");
				return NULL;
		}

		$this->set_extension($ext);
		$this->set_image_type($data['mime']);

		$original_name = translite_uri_simple(preg_replace('/\.\w+$/', '', $this->original_filename()));
		$upload_file_name = defval($data, 'file_name', sprintf('%06d', $this->id()).'-'.$original_name.'.'.$this->extension());
		$this->set_file_name($upload_file_name);

		mkpath($this->image_dir(), 0777);
		if(!file_exists($this->image_dir()))
			bors_throw("Can't create dir '{$this->image_dir()}'<br/>");
		if(!is_writable($this->image_dir()))
			bors_throw("Can't write dir '{$this->image_dir()}'<br/>");
		if(!move_uploaded_file($file, $this->file_name_with_path()))
			bors_throw("Can't load image {$data['name']}<br/>");
		@chmod($this->file_name_with_path(), 0666);

		$this->recalculate(true);

		return $this;
	}

	// Регистрация файла по абсолютному или относительному к DOCUMENTS_ROOT пути.
	// Возвращает объект изображения.
	static function register_file($file, $new_instance = true, $exists_check = true, $class_name = NULL)
	{
		$ch = new bors_cache_fast(NULL);
		if($l = $ch->get('bors_image_register', $file))
			return $l;

		if(!$class_name)
		{
			if(function_exists('get_called_class'))
				$class_name = get_called_class();
			else
				$class_name = 'bors_image';
		}

		$img = bors_new($class_name, NULL);

		$data = url_parse($file);

		if($exists_check && $img2 = bors_find_first($class_name, array('full_file_name' => $data['local_path'])))
			return $ch->set($img2, rand(3600, 86400));

		$img->set_original_filename(basename($file), $new_instance);
//		$img->set_relative_path(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($file)), $new_instance);
		// Потом после рефакторинга этот хардкод уйдёт.
		$img->set_relative_path(preg_replace('!/var/www/[^/]+/htdocs/!', '', dirname($file)), $new_instance);
		$img->set_full_file_name($data['local_path'], $new_instance);
		$img->set_extension(preg_replace('!^.+\.([^\.]+)$!', '$1', $img->original_filename()), $new_instance);
		$img->set_file_name($img->original_filename(), $new_instance);

		$img->set_hash_y($img->hash_grayscale(), $new_instance);
		list($hr, $hg, $hb) = $img->hash_rgb();
		$img->set_hash_r($hr, $new_instance);
		$img->set_hash_g($hg, $new_instance);
		$img->set_hash_b($hb, $new_instance);

		@chmod($img->image_dir(), 0777);
		@chmod($img->file_name_with_path(), 0666);

		$img->recalculate($new_instance);

		if($new_instance)
			$img->new_instance();

		return $ch->set($img, rand(3600, 86400));
	}

	function cross_objects($to_class = NULL) { return bors_link::objects($this); }

	function delete()
	{
		@unlink($this->file_name_with_path());

		if($this->full_file_name())
			@unlink($this->full_file_name());

		@rmdir($this->image_dir());

		return parent::delete();
	}

	function _class_title_def() { return ec('Изображение'); }
	function class_title_vp() { return ec('изображение'); }
	function _class_title_rp_def() { return ec('изображения'); }

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
		{
			config_set('bors-image-lasterror', "Image '$file' not exists");
			return false;
		}

		@header('Content-type: ' . $this->mime_type());
		@header('Content-Length: ' . filesize($file));
		echo file_get_contents($file);
		return true;
	}

	function parent_object() { return object_load($this->parent_class_id(), $this->parent_object_id()); }

//	WHY?
//	function can_cached() { return false; }

	function setdefaultfor_url($obj)  { return "/admin/tools/set-default/?object={$obj->internal_uri()}&image={$this->internal_uri()}"; }
	function imaged_set_default_url($object, $title = NULL)
	{
		if($title === NULL)
			$title = ec('Сделать изображением по умолчанию');
		return "<a href=\"".$this->setdefaultfor_url($object)."\"><img src=\"/_bors/i/set-default-16.gif\" width=\"16\" height=\"16\" alt=\"def\" title=\"$title\"/></a>";
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

	function bb_code($append = '')
	{
		return "[img bors_image://{$this->id()}".($append?' '.$append:'')."]";
	}

	function set_parent_object($object)
	{
		$this->set_parent_class_id($object->class_id());
		$this->set_parent_object_id($object->id());
	}

	function clear_thumbnails()
	{
		bors_debug::syslog('000-heavy-code', "Call heavy thumbnail clear for ".$this);

		//TODO: придумать избавление от такого издевательства.
		$thumbnails = bors_find_all('bors_image_thumb', array(
			"id LIKE '".intval($this->id()).",%'",
			"full_file_name LIKE '%/".addslashes(basename($this->full_file_name()))."'",
		));

		if($thumbnails)
			foreach($thumbnails as $t)
				$t->delete();
	}

	function hash_grayscale()
	{
		// http://habrahabr.ru/post/120562/
		// http://habrahabr.ru/post/143689/
		$source = imagecreatefromjpeg($this->full_filename());
		$hash = imagecreatetruecolor(8, 8);
		imagecopyresampled($hash, $source, 0, 0, 0, 0, 8, 8, imagesx($source), imagesy($source));
		imagefilter($hash, IMG_FILTER_GRAYSCALE);

		$map = array();
		$arr = array();

		for($y=0; $y<8; $y++)
			for($x=0; $x<8; $x++)
				$arr[] = $map[$y][$x] = imagecolorat($hash, $x, $y) & 0xFF;

		sort($arr);
		$median = ($arr[31]+$arr[32])/2;

		$mhash = 0;

		for($y=0; $y<8; $y++)
			for($x=0; $x<8; $x++)
				$mhash = ($mhash<<2) | ($map[$y][$x] > $median ? 1 : 0);

		return $mhash;
	}

	function hash_rgb()
	{
		$source = imagecreatefromjpeg($this->full_filename());
		$hash = imagecreatetruecolor(8, 8);
		imagecopyresampled($hash, $source, 0, 0, 0, 0, 8, 8, imagesx($source), imagesy($source));

		$rmap = array();
		$gmap = array();
		$bmap = array();
		$rarr = array();
		$garr = array();
		$barr = array();

		for($y=0; $y<8; $y++)
		{
			for($x=0; $x<8; $x++)
			{
				$rarr[] = $rmap[$y][$x] = (imagecolorat($hash, $x, $y) >> 16) & 0xFF;
				$garr[] = $gmap[$y][$x] = (imagecolorat($hash, $x, $y) >> 8) & 0xFF;
				$barr[] = $bmap[$y][$x] =  imagecolorat($hash, $x, $y) & 0xFF;
			}
		}

		sort($rarr);
		sort($garr);
		sort($barr);

		$rmedian = ($rarr[31]+$rarr[32])/2;
		$gmedian = ($garr[31]+$garr[32])/2;
		$bmedian = ($barr[31]+$barr[32])/2;

		$rhash = 0;
		$ghash = 0;
		$bhash = 0;

		for($y=0; $y<8; $y++)
		{
			for($x=0; $x<8; $x++)
			{
				$rhash = ($rhash<<2) | ($rmap[$y][$x] > $rmedian ? 1 : 0);
				$ghash = ($ghash<<2) | ($gmap[$y][$x] > $gmedian ? 1 : 0);
				$bhash = ($bhash<<2) | ($bmap[$y][$x] > $bmedian ? 1 : 0);
			}
		}

		return array($rhash, $ghash, $bhash);
	}
}
