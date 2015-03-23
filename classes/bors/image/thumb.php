<?php

// 	Основной класс.

require_once('inc/images.php');

class bors_image_thumb extends bors_image
{
	function db_name() { return config('cache_database'); }
	function table_name() { return 'bors_pictures_thumbs'; }
	function can_be_empty() { return true; }

	private $geo_width, $geo_height, $geo_opts, $geometry, $original;

	function image_class() { return 'bors_image'; }

	function table_fields()
	{
		return array(
			'id',
			'full_file_name',
			'relative_path',
			'file_name',
			'original_filename',
			'width',
			'height',
			'size',
			'extension',
			'mime_type',
			'create_time',
			'modify_time',
		);
	}

	function replace_on_new_instance() { return true; }

	function cache_static_root() { return $_SERVER['DOCUMENT_ROOT']; }

	function data_load()
	{
		if(is_numeric($this->id()) && $this->args('geometry'))
			$this->set_id($this->id().','.$this->args('geometry'));

		if(config('cache_database'))
			parent::data_load();

		if(preg_match('!^(\d+),((\d*)x(\d*))$!', $this->id(), $m))
		{
			$id = $m[1];
			$this->geometry   = $m[2];
			$this->geo_width  = $m[3];
			$this->geo_height = $m[4];
		}
		elseif(preg_match('!^(\d+),((\d*)x(\d*)\(([^)]+)\))$!', $this->id(), $m))
		{
			$id = $m[1];
			$this->geometry   = $m[2];
			$this->geo_width  = $m[3];
			$this->geo_height = $m[4];
			$this->geo_opts   = $m[5];
		}
		else
			return $this->set_is_loaded(false);

		if($this->width() && file_exists($this->file_name_with_path()) && substr($this->file_name_with_path(),-1) != '/')
			return $this->set_is_loaded(true);

		$this->set_height(1); // трассировка

		$this->original = bors_load($this->arg('image_class_name', $this->image_class()), $id);

		if(!$this->original)
			return $this->set_is_loaded(false);

		$this->set_height(2); // трассировка

		// Тут было $this->original->file_name()
		// Не ошибка ли? Если возвращать, то проверить WWW.aviaport_pictures на предмет соответствий
		// Было сделано update aviaport_pictures set original_filename = file_name where original_filename = '';
		if(!preg_match('/\.(jpe?g|gif|png)$/i', $this->original->file_name()))
			return $this->set_is_loaded(false);

//		$this->delete();

		$this->set_height(3); // трассировка

		if($original_path = $this->original->relative_path())
			$new_path = secure_path('/cache/'.$original_path.'/'.$this->geometry);
		else
			$new_path = NULL;

		$caching = config('cache_database') ? true : false;

		$this->set_relative_path($new_path, $caching);

		$original_url = $this->original->url();

		// Фикс кривых URL вида http:///sites/ru/de/demotivatorium/sstorage/3/2012/04/1304120411359847.jpg
		if(preg_match('!^http://(/.+)$!', $original_url, $m))
			$original_url = $m[1];

		// Заворачиваем адреса с уже кешем в оригинальные.
		$original_url = preg_replace("!^(.*?)cache/(.+)/\d*x\d*/([^/]+?)$!", "$1$2/$3", $original_url);

//		if(config('is_debug')) var_dump('o', $original_url);

		if($original_url[0] == '/')
			$new_url = '/cache'.preg_replace('!^(/.+?)([^/]+)$!', '${1}'.$this->geometry.'/$2', $original_url);
		else
			$new_url = preg_replace('!^(http://[^/]+)(/.+?)([^/]+)$!', '$1/cache${2}'.$this->geometry.'/$3', $original_url);

		if(!preg_match('!/cache.*/\d*x\d*!', $new_url))
			bors_throw('Incorrect new url '.$new_url.' for '.$this->id().'; original url='.$original_url);

		$this->set_height(4); // трассировка

		$this->set_full_url($new_url, $caching);

		foreach(explode(' ', 'extension title alt description author_name image_type') as $key)
			$this->set($key, $this->original->$key(), $caching);

		$this->set_file_name($this->original->file_name(), $caching);

		$file_orig  = $this->original->file_name_with_path();

//		$file_thumb = $this->file_name_with_path();
		// WTF? http://www.balancer.ru/g/p3463879
		$new_url = str_replace('forums.testing.airbase.ru', 'forums.balancer.ru', $new_url);
		$oud = url_parse($new_url);
		if(!$oud['local'] || !$oud['local_path'])
			bors_throw('Unknown local for "'.$new_url.'" thumb: '.print_r($oud, true)
				.'; file_name_with_path='.$this->file_name_with_path());

		$this->set_height(5); // трассировка

		$file_thumb = $oud['local_path'];

		if(!preg_match('!/cache.*/\d*x\d*!', $file_thumb))
			bors_throw('Incorrect thumb file '.$file_thumb.' for '.print_r($oud, true));

		$this->set_height(6); // трассировка

		$abs = false;

		if(!file_exists($file_orig) && !preg_match('!^/var/www/!', $file_orig)) // Заменить хардкод
		{
			$file_orig  = $_SERVER['DOCUMENT_ROOT'] . $file_orig;
			$file_thumb = $_SERVER['DOCUMENT_ROOT'] . $file_thumb;
			$abs = true;
		}

		$file_orig_r = $file_orig;
		if(!($fsize_orig = @filesize($file_orig_r)))
			bors_debug::syslog('invalid-image', "Image '$file_orig_r' size zero");

		$file_thumb_r = $file_thumb;
		//TODO: придумать обработку больших картинок.
		$fsize_thumb = @filesize($file_thumb_r);
		$this->set_full_file_name($file_thumb, $caching);

		$this->set_size($fsize_thumb, $caching);

		if(!$this->original->file_name() || !$fsize_orig)
			return;

		$this->set_height(7); // трассировка

		mkpath($this->image_dir(), 0777);

		if(!$this->thumb_create($abs))
			return $this->set_is_loaded(false);

		$this->set_height(8); // трассировка

//		bors_debug::syslog('000-image-debug', "Get thumb size for ".$file_thumb_r);
		if(!file_exists($file_thumb_r))
			bors_debug::syslog('image-error', 'Image file not exists: ' . $file_thumb_r .'; image_dir='.$this->image_dir());

		$img_data = getimagesize($file_thumb_r);

		bors_debug::syslog('000-image-debug', "Size for ".$file_thumb_r." = ".print_r($img_data, true));

		if(empty($img_data[0]))
			bors_debug::syslog('image-error', 'Cannot get image width for '.$file_thumb_r
				.'; image_dir='.$this->image_dir()
			);

		$this->set_width($img_data[0], $caching);
		$this->set_height($img_data[1], $caching);
		$this->set_mime_type($img_data['mime'], $caching);

		// Принудительно пропишем ID, чтобы он записался по new_instance
		$this->data['id'] = $this->id();
		$this->changed_fields['id'] = NULL;

		if($caching && $img_data[0])
			$this->new_instance();

		$this->set_is_loaded(true);
	}

	private function thumb_create($abs = false)
	{
		if(file_exists($this->file_name_with_path()))
			return true;

		if($abs)
		{
			$at = $_SERVER['DOCUMENT_ROOT'].$this->file_name_with_path();
			$as = $_SERVER['DOCUMENT_ROOT'].$this->original->file_name_with_path();
			$err = image_file_scale($as, $at, $this->geo_width, $this->geo_height, $this->geo_opts);
		}
		else
			$err = image_file_scale($this->original->file_name_with_path(), $this->file_name_with_path(), $this->geo_width, $this->geo_height, $this->geo_opts);

		return $err == NULL;
	}

	function fullsized_url() { $this->data_load(); return $this->original ? "<a href=\"{$this->original->url()}\">{$this->html_code()}</a>" : $this->html_code(); }

	function alt() { return $this->original ? $this->original->alt() : ""; }

	function delete()
	{
		@unlink($file = $this->full_file_name());

		$dir = dirname($file);

		do
		{
			@rmdir($dir);
		} while(!is_dir($dir) && ($dir = dirname($dir)) && $dir != '/');

		parent::delete();
	}
}
