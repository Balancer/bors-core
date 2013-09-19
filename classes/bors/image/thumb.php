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

		//TODO: сделать вариант, совместимый с safe_mod!
//		var_dump($this->width(), $this->file_name_with_path(), $this->data);

//if(!config('is_developer'))
{
		if($this->width() && file_exists($this->file_name_with_path()) && substr($this->file_name_with_path(),-1) != '/')
			return $this->set_is_loaded(true);
}
		$this->original = object_load($this->arg('image_class_name', $this->image_class()), $id);

		if(!$this->original)
			return $this->set_is_loaded(false);

//		$this->delete();

		if($original_path = $this->original->relative_path())
			$new_path = secure_path('/cache/'.$original_path.'/'.$this->geometry);
		else
			$new_path = NULL;

//		if(config('is_developer')) { var_dump($original_path, $new_path); exit(); }

		$caching = config('cache_database') ? true : false;

		$this->set_relative_path($new_path, $caching);

		$original_url = $this->original->url();
		if($original_url[0] == '/')
			$new_url = '/cache'.preg_replace('!^(/.+?)([^/]+)$!', '${1}'.$this->geometry.'/$2', $original_url);
		else
			$new_url = preg_replace('!^(http://[^/]+)(/.+?)([^/]+)$!', '$1/cache${2}'.$this->geometry.'/$3', $original_url);

		if(!preg_match('!/cache.*/\d*x\d*!', $new_url))
			bors_throw('Incorrect new url '.$new_url.' for '.$this->id().'; original url='.$original_url);

//		if(config('is_developer')) { var_dump($original_url, $new_url); exit(); }
//		if(config('is_developer')) { $o=$this->original; var_dump($original_url, $new_url, $o->class_name(), $o->id(), $o->db_name(), $o->table_name()); exit(); }
//		if(config('is_developer')) { var_dump($original_path, $new_path); exit(); }

		$this->set_full_url($new_url, $caching);

		foreach(explode(' ', 'extension title alt description author_name image_type') as $key)
			$this->set($key, $this->original->$key(), $caching);

		$this->set_file_name($this->original->file_name(), $caching);

		$file_orig  = $this->original->file_name_with_path();
//		if(($d = $this->image_dir()) && $d != '/')
//		{
//			$file_thumb = $this->file_name_with_path();
//			if(config('is_developer')) { var_dump($d, $file_thumb); exit(); }
//		}
//		else
		{
//			$file_thumb = $this->file_name_with_path();
			$oud = url_parse($new_url);
			if(!$oud['local'] || !$oud['local_path'])
				bors_throw('Unknown local for thumb: '.print_r($oud, true));

//			if(config('is_developer')) { var_dump($oud, $file_thumb); exit(); }
			$file_thumb = $oud['local_path'];

			if(!preg_match('!/cache.*/\d*x\d*!', $file_thumb))
				bors_throw('Incorrect thumb file '.$file_thumb.' for '.print_r($oud, true));

		}

		$abs = false;
//		if(config('is_developer')) var_dump($this->full_file_name(), $this->file_name_with_path(), $file_thumb);
		if(!file_exists($file_orig) && !preg_match('!^/var/www/!', $file_orig)) // Заменить хардкод
		{
			$file_orig  = $_SERVER['DOCUMENT_ROOT'] . $file_orig;
			$file_thumb = $_SERVER['DOCUMENT_ROOT'] . $file_thumb;
			$abs = true;
		}

		if(config('pics_base_safemodded'))
		{
			$file_orig_r = str_replace(config('pics_base_dir'), config('pics_base_url'), $file_orig);
			//TODO: ужасно, но пока только так.
			$fsize_orig = strlen(@file_get_contents($file_orig_r));

			$file_thumb_r = str_replace(config('pics_base_dir'), config('pics_base_url'), $file_thumb);
			//TODO: ужасно, но пока только так.
			$fsize_thumb = strlen(file_get_contents($file_thumb_r));
			//TODO: а это  совсем жопа
			$this->set_full_file_name(str_replace('http://pics.aviaport.ru/', '/var/www/pics.aviaport.ru/htdocs/', $file_thumb_r), true);
		}
		else
		{
			$file_orig_r = $file_orig;
			if(!($fsize_orig = @filesize($file_orig_r)))
				debug_hidden_log('invalid-image', "Image '$file_orig_r' size zero");

			$file_thumb_r = $file_thumb;
			//TODO: придумать обработку больших картинок.
			$fsize_thumb = @filesize($file_thumb_r);
			$this->set_full_file_name($file_thumb, $caching);
		}

		$this->set_size($fsize_thumb, $caching);

		if(!$this->original->file_name() || !$fsize_orig)
			return;

		mkpath($this->image_dir(), 0777);

//		debug_hidden_log('000', "$file_orig_r :".@filesize($file_orig_r));

		if(!$this->thumb_create($abs))
			return $this->set_is_loaded(false);

		$img_data = @getimagesize($file_thumb_r);
		if(empty($img_data[0]))
			debug_hidden_log('image-error', 'Cannot get image width for '.$file_thumb_r
				.'; image_dir='.$this->image_dir()
			);

		$this->set_width($img_data[0], $caching);
		$this->set_height($img_data[1], $caching);
		$this->set_mime_type($img_data['mime'], $caching);

		if($caching && $img_data[0])
			$this->new_instance();

		//TODO: странный костыль.
//		if($caching)
//		{
//			$prev = bors_find_first($this->class_name(), array(
//				'full_file_name' => $this->full_file_name(),
//			));

//			if($prev)
//				$prev->delete();
//		}

//		echo "File {$this->file_name_with_path()}, size=$fsize_thumb<br />\n"; exit();

//		echo "{$this}: {$this->wxh()}<br />\n";
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
