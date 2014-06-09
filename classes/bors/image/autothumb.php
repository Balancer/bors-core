<?php

/*
	Автоматическая генерация превьюшек для /cache/
*/

class bors_image_autothumb extends bors_object
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

		// Если убиваем кеш, то стереть файл
		if(array_key_exists('nc', $_GET) && file_exists($f = $_SERVER['DOCUMENT_ROOT'].'/cache'.$thumb_path))
			unlink($f);

		$origin_path = $m[1].$m[3];
//		if(config('is_debug')) { echo '<xmp>', $_SERVER['DOCUMENT_ROOT'] . $origin_path, print_r($m, true), PHP_EOL; var_dump($thumb_path, $origin_path); exit(); }
		if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $origin_path))
		{
			// http://www.balancer.ru/sites/u/p/upload.wikimedia.org/wikipedia/commons/b/b0/_quote_Facing_the_Flag_quote__by_L%C3%A9on_Benett_34.jpg
			if(!preg_match('/%/', $origin_path))
				return;
			$origin_path = urldecode($origin_path);

			if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $origin_path))
				return;
		}

		$this->geo = $m[2];
//		if(config('is_debug')) { echo $_SERVER['DOCUMENT_ROOT'] . $origin_path, print_r($m, true), PHP_EOL; exit(); }

		parent::__construct($this->origin_path = $origin_path);
	}

	function is_loaded()
	{
		return $this->origin_path
			&& !preg_match('/\.(bmp|php)/', $this->origin_path);
	}

	function can_be_empty() { return false; }

	function make_self()
	{
		$rel  = dirname($this->origin_path);
		$file = basename($this->origin_path);
		$img = bors_find_first('bors_image', array('relative_path' => $rel, 'file_name' => $file));

		if(!$img || !file_exists($img->file_name_with_path()))
			$img = bors_image::register_file($this->origin_path);

		if(preg_match('!^/!', $u = $img->url()))
		{
			$ud = parse_url(bors()->request()->url());

			if(!empty($ud['host']))
				$img->set_full_url("http://{$ud['host']}$u");
			else
				$img->set_full_url($u);
		}

		return $img->thumbnail($this->geo);
	}

	function pre_show()
	{
		$rel  = dirname($this->origin_path);
		$file = basename($this->origin_path);

//		$caching = config('cache_database') ? true : false;

		$img = bors_find_first('bors_image', array('relative_path' => $rel, 'file_name' => $file));

		if(config('bors.version_show') && $img)
			header('X-original-image: '.$img->internal_uri());

		if(!$img || !file_exists($img->file_name_with_path()))
		{
			$img = bors_image::register_file($this->origin_path);
		}

		if(preg_match('!^/!', $u = $img->url()))
		{
			$ud = parse_url(bors()->request()->url());
			if(empty($ud['host']))
				$img->set_full_url($u);
			else
				$img->set_full_url("http://{$ud['host']}$u");
		}

		$thumb = $img->thumbnail($this->geo);

		if(config('bors.version_show'))
		{
			header('X-thumb-image: '.$thumb->internal_uri());
			header('X-thumb-class: '.$thumb->class_name());
			header('X-thumb-file: '.$thumb->static_file());
		}

		if($thumb->pre_show())
			return true;

		@list($width, $height) = explode('x', $this->geo);

		require_once('inc/bors/bors_images.php');
		$msg = ec("Ошибка изображения {$thumb->class_name()}({$thumb->id()}):\n").config('bors-image-lasterror');
		debug_hidden_log('image-thumb-error', "geo={$this->geo}, img={$img}; $msg");
		bors_image_message($msg, array(
			'print' => true,
			'width' => $width ? $width : 640,
			'height' => $height ? $height: 400,
		));
		config_set('bors-image-lasterror', NULL);

		return true;
	}

	function body() { return NULL; }
}
