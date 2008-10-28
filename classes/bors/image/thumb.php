<?php

require_once('inc/images.php');

class bors_image_thumb extends bors_image
{
	function main_table_storage() { return 'bors_pictures_thumbs'; }
	function main_db_storage() { return config('cache_database'); }
	function can_be_empty() { return true; }
	
	private $geo_width, $geo_height, $geo_opts, $geometry, $original;

	function fields()
	{
		return array($this->main_db_storage() => array($this->main_table_storage() => array(
			'id',
			'relative_path',
			'file_name',
			'original_filename',
			'width',
			'height',
			'size',
			'extension',
			'mime_type',
		)));
	}

	function init()
	{
		if(is_numeric($this->id()) && $this->args('geometry'))
			$this->set_id($this->id().','.$this->args('geometry'));
	
		parent::init();
		
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
			return $this->set_loaded(false);

		$this->original = object_load('bors_image', $id);

		if(!$this->original)
			return $this->set_loaded(false);
		
		if($this->width() && file_exists($this->file_name_with_path()))
			return $this->set_loaded(true);

		$this->delete();
		
		$new_path = secure_path('/cache/'.$this->original->relative_path().'/'.$this->geometry);
		
		$this->new_instance();

		$this->set_relative_path($new_path, true);
			
		foreach(explode(' ', 'extension title alt description author_name image_type') as $key)
			$this->set($key, $this->original->$key(), true);

		$this->set_file_name($this->original->file_name(), true);

		$file = $this->original->file_name_with_path();
		$abs = false;
		if(!file_exists($file))
		{
			$file = $_SERVER['DOCUMENT_ROOT'] . $file;
			$abs = true;
		}

		if(config('pics_base_safemodded'))
		{
			$rfile = str_replace(config('pics_base_dir'), config('pics_base_url'), $file);
			$fsize = strlen(file_get_contents($rfile));
		}
		else
		{
			$rfile = $file;
			$fsize = filesize($file);
		}

//		echo "size of ".$this->original->file_name()." = $fsize<br/>\n";
		if(!$this->original->file_name() || !$fsize)
			return;

		mkpath($this->image_dir(), 0777);
		$this->thumb_create($abs);

//		echo "File {$this->file_name_with_path()}<br />\n"; exit();
		$this->set_size($fsize, true);

		$img_data = getimagesize($rfile);

		$this->set_width($img_data[0], true);
		$this->set_height($img_data[1], true);
		$this->set_mime_type($img_data['mime'], true);

//		echo "{$this}: {$this->wxh()}<br />\n";
		$this->set_loaded(true);
	}

	private function thumb_create($abs = false)
	{
		if(file_exists($this->file_name_with_path()))
			return;

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

	function fullsized_url() { return "<a href=\"{$this->original->url()}\">{$this->html_code()}</a>"; }

	function alt() { return $this->original->alt(); }

	function url() { return secure_path(config('pics_base_url').$this->relative_path().'/'.$this->file_name()); }
	
	function replace_on_new_instance() { return true; }
}
