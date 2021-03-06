<?php

// Возможно, устаревший, по сравнению с bors_image_thumb класс
// Используется только в bors_image_file?
// На пробу убран из bors_image_file 18.09.2013

require_once('inc/images.php');

class bors_image_thumbnail extends bors_image
{
	function db_name() { return config('cache_database'); }
	function table_name() { return 'bors_pictures_thumbs'; }
	function can_be_empty() { return true; }

	private $geo_width, $geo_height, $geo_opts, $geometry, $original;

	function default_image_class() { return 'bors_image'; }

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
		elseif(preg_match('!^(\w+):(.+?)$!', $this->id(), $m))
		{
			$this->original = bors_load($m[1]);
			if(!$this->original)
				bors_throw("Can't load original image '{$m[1]}' for thumbnail");

			$this->geometry   = $m[2];
			$this->geometry_parse();
		}
		else
			return $this->set_is_loaded(false);

		//TODO: сделать вариант, совместимый с safe_mod!
		if($this->width() && file_exists($this->file_name_with_path()) && substr($this->file_name_with_path(),-1) != '/')
			return $this->set_is_loaded(true);

		if(empty($this->original))
			$this->original = bors_load($this->default_image_class(), $id);

		if(!$this->original)
			return $this->set_is_loaded(false);

		$caching = config('cache_database') ? true : false;
		if($caching)
			$this->new_instance();

		if($original_path = $this->original->relative_path())
			$new_path = secure_path('/cache/'.$original_path.'/'.$this->geometry);
		else
			$new_path = NULL;

		$this->set_relative_path($new_path, $caching);

		if($original_url = $this->original->full_url())
			$new_url = preg_replace('!^(http://[^/]+)(/.+?)([^/]+)$!', '$1/cache${2}'.$this->geometry.'/$3', $original_url);
		else
			$new_url = NULL;

		$this->set_full_url($new_url, $caching);

		foreach(explode(' ', 'extension title alt description author_name image_type') as $key)
			$this->set($key, $this->original->$key(), $caching);

		$this->set_file_name($this->original->file_name(), $caching);

		$file_orig  = $this->original->file_name_with_path();
		$file_thumb = $this->file_name_with_path();
		$abs = false;
		if(!file_exists($file_orig))
		{
			$file_orig  = $_SERVER['DOCUMENT_ROOT'] . $file_orig;
			$file_thumb = $_SERVER['DOCUMENT_ROOT'] . $file_thumb;
			$abs = true;
		}

		$file_orig_r = $file_orig;
		if(!($fsize_orig = @filesize($file_orig_r)))
			bors_debug::syslog('invalid-image', "Image '$file_orig_r' size zero");

		if(!$this->original->file_name() || !$fsize_orig)
			return;

		mkpath($this->image_dir(), 0777);

		if(!$this->thumb_create($abs))
			return $this->set_is_loaded(false);

		$file_thumb_r = $file_thumb;
		//TODO: придумать обработку больших картинок.
		$fsize_thumb = @filesize($file_thumb_r);
		$this->set_full_file_name($file_thumb, true);

//		echo "File {$this->file_name_with_path()}, size=$fsize_thumb<br />\n"; exit();
		$this->set_size($fsize_thumb, $caching);

		bors_debug::syslog('000-image-debug', "Get thumbnail size for ".$file_thumb_r);
		$img_data = @getimagesize($file_thumb_r);
		if(empty($img_data[0]))
			bors_debug::syslog('image-error', 'Cannot get image width');

		$this->set_width($img_data[0], $caching);
		$this->set_height($img_data[1], $caching);
		$this->set_mime_type($img_data['mime'], $caching);

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

	function replace_on_new_instance() { return true; }

	function geometry_parse()
	{
		if(preg_match('!^(\d*)x(\d*)$!', $this->geometry, $m))
		{
			$this->geo_width  = $m[1];
			$this->geo_height = $m[2];
			$this->geo_opts   = NULL;
		}
		elseif(preg_match('!^(\d*)x(\d*)\(([^)]+)\)$!', $this->geometry, $m))
		{
			$this->geo_width  = $m[1];
			$this->geo_height = $m[2];
			$this->geo_opts   = $m[3];
		}
		else
			bors_throw('Unknonwn image geometry: '.$this->geometry);
	}
}
