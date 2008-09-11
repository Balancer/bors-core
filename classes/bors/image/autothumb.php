<?php

class bors_image_autothumb extends base_object
{
	var $origin_path = NULL;
	var $geo = NULL;

	function __construct($thumb_path)
	{
		if(!preg_match('!^(/.*/)(\d*x\d*)/([^/]+)$!', $thumb_path, $m))
			if(!preg_match('!^(/.*/)(\d*x\d*\([^)]+\))/([^/]+)$!', $thumb_path, $m))
				return;
		
		$origin_path = $m[1].$m[3];
		$this->geo = $m[2];
		
		if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $origin_path))
			return;
		
		parent::__construct($this->origin_path = $origin_path);
	}
	
	function loaded() { return $this->origin_path; }
	function can_be_empty() { return false; }
	
	function pre_show()
	{
		$rel  = dirname($this->origin_path);
		$file = basename($this->origin_path);
		$img = objects_first('bors_image', array('relative_path' => $rel, 'file_name' => $file));
		if(!$img)
		{
			$img = object_new('bors_image');
			$img->register_file($this->origin_path);
			$img->new_instance();
		}
		
		$thumb = $img->thumbnail($this->geo);
		return $thumb->pre_show();
	}
}
