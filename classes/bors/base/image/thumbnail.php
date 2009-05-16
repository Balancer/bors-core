<?php

class base_image_thumbnail extends base_image
{
	private $geo_width, $geo_height, $geometry, $original;

	function init()
	{
		@list($id, $this->geometry) = explode(',', $this->id());
	
		list($this->geo_width, $this->geo_height) = @split('x', $this->geometry);
		
		$this->original = object_load('base_image', $id);
		
		if($this->width())
		{
			$this->set_loaded(true);
			return false;
		}

		$this->set_create_time(time(), true);
		$this->set_modify_time(time(), true);

//		$this->set_relative_path('cache/'.$this->original->relative_path().'/'.($this->id()%100).'/'.$this->geometry, true);
		$this->set_relative_path('cache/'.$this->original->relative_path().'/'.$this->geometry, true);
			
		foreach(explode(' ', 'extension title alt description author_name image_type') as $key)
			$this->set($key, $this->original->$key(), true);

//		$this->set_file_name($this->id().'.'.$this->extension(), true);
		$this->set_file_name($this->original->file_name(), true);

//		echo "size of ".$this->original->file_name()." = ".filesize($this->original->file_name_with_path())."<br/>\n";
		if(!$this->original->file_name() || !@filesize($this->original->file_name_with_path()))
			return false;

		mkpath($this->image_dir(), 0777);
		@chmod($this->image_dir(), 0777);
		$this->thumb_create($this->geometry);

//		echo "File {$this->file_name_with_path()}<br />\n";
		$this->set_size(filesize($this->file_name_with_path()), true);

		$img_data = getimagesize($this->file_name_with_path());

		$this->set_width($img_data[0], true);
		$this->set_height($img_data[1], true);
		$this->set_mime_type($img_data['mime'], true);

//		echo "{$this}: {$this->wxh()}<br />\n";
		$this->set_loaded(true);

		return false;
	}

	function thumb_create($geometry)
	{
		if(file_exists($this->file_name_with_path()))
			return;
			
		$this->thumb_file_create($geometry);
	}

	function thumb_file_create($geometry)
	{
		@list($w, $h) = explode('x', $geometry);
		$err = image_file_scale($this->original->file_name_with_path(), $this->file_name_with_path(), $w, $h);
		return $err == NULL;
	}

	function fullsized_url() { return "<a href=\"{$this->original->url()}\">{$this->html_code()}</a>"; }
}
