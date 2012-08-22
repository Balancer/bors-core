<?php

class bors_image_autothumb extends base_object
{
	var $origin_path = NULL;
	var $geo = NULL;

	function __construct($thumb_path)
	{
		if(preg_match('/%D0/', $thumb_path))
			$thumb_path = urldecode($thumb_path);

		if(!preg_match('!^(/.*/)(\d*x\d*)/([^/]+)$!', $thumb_path, $m))
			if(!preg_match('!^(/.*/)(\d*x\d*\([^)]+\))/([^/]+)$!', $thumb_path, $m))
				return;

		$origin_path = $m[1].$m[3];
		$this->geo = $m[2];

		if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $origin_path))
			return;

		parent::__construct($this->origin_path = $origin_path);
	}

	function loaded()
	{
		return $this->origin_path
			&& !preg_match('/\.(bmp|php)$/', $this->origin_path);
	}

	function can_be_empty() { return false; }

	function pre_show()
	{
		$rel  = dirname($this->origin_path);
		$file = basename($this->origin_path);

//		$caching = config('cache_database') ? true : false;

		$img = objects_first('bors_image', array('relative_path' => $rel, 'file_name' => $file));

		if(!$img || !file_exists($img->file_name_with_path()))
			$img = bors_image::register_file($this->origin_path);

		$thumb = $img->thumbnail($this->geo);
		if($thumb->pre_show())
			return true;

		@list($width, $height) = explode('x', $this->geo);
		require_once('inc/bors/bors_images.php');
		bors_image_message(ec("Ошибка изображения:\n").config('bors-image-lasterror'), array(
			'print' => true,
			'width' => $width ? $width : 100,
			'height' => $height ? $height: 100,
		));
		config_set('bors-image-lasterror', NULL);

		debug_hidden_log('image-thumb-error', "geo={$this->geo}, img={$img}");
		return true;
	}

	function body() { return NULL; }
}
