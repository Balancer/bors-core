<?php

class base_image extends base_object
{
	function can_be_empty() { return false; }

	function wxh()
	{
		if($this->width() == 0 || $this->height() == 0)
			$this->recalculate(true);

		$w = $this->width() ? "width=\"{$this->width()}\"" : "";
		$h = $this->height() ? "height=\"{$this->height()}\"" : "";

		return  "{$h} {$w} alt=\"[image]\" title=\"".str_replace('&amp;#', '&#', htmlspecialchars($this->alt_or_description()))."\"";
	}

	function html() { return $this->html_code(); }
	function html_code($append = "") { return "<img src=\"{$this->url()}\" {$this->wxh()} $append />"; }

	function thumbnail($geometry) { return object_load('base_image_thumbnail', $this->id().','.$geometry); }

	function recalculate($db_update)
	{
		$x = @getimagesize($this->url());
		if(!$x)
			$x = @getimagesize($this->file_name_with_path());
		$this->set_width($x[0], $db_update);
		$this->set_height($x[1], $db_update);
		$this->set_size(@filesize($this->file_name_with_path()), $db_update);
		$this->set_mime_type($x['mime'], $db_update);
		$this->set_extension(preg_replace('!^.+\.([^\.]+)$!', '$1', $this->original_filename()), $db_update);
		$this->store();
	}

	function upload($data, $dir)
	{
		$this->set_original_filename($data['name'], true);

		$this->set_relative_path($dir.'/'.$this->id()%100, true);
		$this->set_extension(preg_replace('!^.+\.([^\.]+)$!', '$1', $this->original_filename()), true);
		$this->set_file_name($this->id().'.'.$this->extension(), true);

		mkpath($this->image_dir(), 0777);
		move_uploaded_file($data['tmp_name'], $this->file_name_with_path());

		$this->recalculate(true);

		return $this;
	}

	function cross_objects() { return bors_link::objects($this); }

	function delete()
	{
		@unlink($this->file_name_with_path());
		@rmdir($this->image_dir());

		return parent::delete();
	}

	function class_title() { return ec('изображение'); }

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
}
