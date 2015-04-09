<?php

// 	Основной класс.

require_once('inc/images.php');

class b2_image_thumb extends bors_image
{
	function db_name() { return config('cache_database'); }
	function table_name() { return 'b2_image_thumbs'; }
	function can_be_empty() { return true; }

	private $geo_width, $geo_height, $geo_opts, $geometry, $original;

	function image_class() { return 'bors_image'; }

	function table_fields()
	{
		return array(
			'id' => 'image_class_name,image_id,geometry',
			'image_class_name',
			'image_id',
			'geometry',
			'full_file_name',
			'relative_path',
			'file_name',
			'width',
			'height',
			'size',
			'extension',
			'mime_type',
			'create_time' => 'UNIX_TIMESTAMP(`create_ts`)',
			'modify_time' => 'UNIX_TIMESTAMP(`modify_ts`)',
		);
	}

	function replace_on_new_instance() { return true; }

	function cache_static_root() { return $_SERVER['DOCUMENT_ROOT']; }

	function url()
	{
		return str_replace('/var/www/', 'http://', str_replace('/htdocs/', '/', $this->full_file_name()));
	}

	function data_load()
	{
		$image_class_name = $this->args('image_class_name');
		$image_id = $this->args('image_id');
		$geometry = $this->args('geometry');

		$aid = compact('image_class_name', 'image_id', 'geometry');
		$thumb = bors_find_first(get_called_class(), $aid);

		if(!$thumb)
			$thumb = bors_new(get_called_class(), $aid);

		$thumb->geometry   = $geometry;

		if(preg_match('!^(\d*)x(\d*)$!', $geometry, $m))
		{
			$thumb->geo_width  = $m[1];
			$thumb->geo_height = $m[2];
		}
		elseif(preg_match('!^(\d*)x(\d*)\(([^)]+)\)$!', $geometry, $m))
		{
			$thumb->geo_width  = $m[1];
			$thumb->geo_height = $m[2];
			$thumb->geo_opts   = $m[3];
		}
		else
			return $thumb->set_is_loaded(false);

		if($thumb->width() && file_exists($thumb->file_name_with_path()) && substr($thumb->file_name_with_path(),-1) != '/')
		{
			$thumb->set_is_loaded(true);
			return $thumb;
		}

		$thumb->set_height(1); // трассировка

		$thumb->original = bors_load($this->arg('image_class_name', $this->image_class()), $image_id);

		if(!$thumb->original)
			return $thumb->set_is_loaded(false);

		$thumb->set_height(2); // трассировка

		// Тут было $thumb->original->file_name()
		// Не ошибка ли? Если возвращать, то проверить WWW.aviaport_pictures на предмет соответствий
		// Было сделано update aviaport_pictures set original_filename = file_name where original_filename = '';
		if(!preg_match('/\.(jpe?g|gif|png)$/i', $thumb->original->file_name()))
			return $thumb->set_is_loaded(false);

//		$thumb->delete();

		$thumb->set_height(3); // трассировка

		if($original_path = $thumb->original->relative_path())
			$new_path = secure_path('/cache/'.$original_path.'/'.$thumb->geometry);
		else
			$new_path = NULL;

		$caching = config('cache_database') ? true : false;

		$thumb->set_relative_path($new_path, $caching);

		$original_url = $thumb->original->url();

		// Заворачиваем адреса с уже кешем в оригинальные.
		$original_url = preg_replace("!^(.*?)cache/(.+)/\d*x\d*/([^/]+?)$!", "$1$2/$3", $original_url);

		if($original_url[0] == '/')
			$new_url = '/cache'.preg_replace('!^(/.+?)([^/]+)$!', '${1}'.$thumb->geometry.'/$2', $original_url);
		else
			$new_url = preg_replace('!^(http://[^/]+)(/.+?)([^/]+)$!', '$1/cache${2}'.$thumb->geometry.'/$3', $original_url);

		if(!preg_match('!/cache.*/\d*x\d*!', $new_url))
			bors_throw('Incorrect new url '.$new_url.' for '.$thumb->id().'; original url='.$original_url);

		$thumb->set_height(4); // трассировка

		$thumb->set_full_url($new_url, $caching);

		foreach(explode(' ', 'extension title alt description author_name image_type') as $key)
			$thumb->set($key, $thumb->original->$key(), $caching);

		$thumb->set_file_name($thumb->original->file_name(), $caching);

		$file_orig  = $thumb->original->file_name_with_path();

//		$file_thumb = $thumb->file_name_with_path();
		// WTF? http://www.balancer.ru/g/p3463879
		$new_url = str_replace('forums.testing.airbase.ru', 'forums.balancer.ru', $new_url);
		$oud = url_parse($new_url);
		if(!$oud['local'] || !$oud['local_path'])
			bors_throw('Unknown local for "'.$new_url.'" thumb: '.print_r($oud, true)
				.'; file_name_with_path='.$thumb->file_name_with_path());

		$thumb->set_height(5); // трассировка

		$file_thumb = $oud['local_path'];

		if(!preg_match('!/cache.*/\d*x\d*!', $file_thumb))
			bors_throw('Incorrect thumb file '.$file_thumb.' for '.print_r($oud, true));

		$thumb->set_height(6); // трассировка

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
		$thumb->set_full_file_name($file_thumb, $caching);

//		echo "\nfo=$file_orig_r\n";
//		echo "\nfr=$file_thumb_r\n";

		$thumb->set_size($fsize_thumb, $caching);

		if(!$thumb->original->file_name() || !$fsize_orig)
			return $thumb->set_is_loaded(false);

		$thumb->set_height(7); // трассировка

		mkpath($thumb->image_dir(), 0777);

		/* =================================== */
		/* =      Собственно, генерация      = */
		/* =================================== */
		if(!$thumb->thumb_create($abs))
			return $thumb->set_is_loaded(false);
		/* =================================== */

		$thumb->set_height(8); // трассировка

//		bors_debug::syslog('000-image-debug', "Get thumb size for ".$file_thumb_r);
		if(!file_exists($file_thumb_r))
			bors_debug::syslog('image-error', 'Image file not exists: ' . $file_thumb_r .'; image_dir='.$thumb->image_dir());

		$img_data = getimagesize($file_thumb_r);

		bors_debug::syslog('000-image-debug', "Size for ".$file_thumb_r." = ".print_r($img_data, true));

		if(empty($img_data[0]))
			bors_debug::syslog('image-error', 'Cannot get image width for '.$file_thumb_r
				.'; image_dir='.$thumb->image_dir()
			);

		$thumb->set_width($img_data[0], $caching);
		$thumb->set_height($img_data[1], $caching);
		$thumb->set_mime_type($img_data['mime'], $caching);

		// Принудительно пропишем ID, чтобы он записался по new_instance
		$thumb->data['id'] = $thumb->id();
		$thumb->changed_fields['id'] = NULL;

		$thumb->set_is_loaded(true);

		return $thumb;
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
