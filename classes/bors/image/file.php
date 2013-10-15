<?php

class bors_image_file extends bors_object
{
	function thumbnail_class() { return 'bors_image_thumb'; }

	function thumbnail($geometry)
	{
		return bors_load($this->thumbnail_class(), $this->internal_uri_ascii().':'.$geometry);
	}

	function file_name_with_path() { return $_SERVER['DOCUMENT_ROOT'].$this->id(); }
	function relative_path() { return $this->id(); }
	function full_url() { return $this->id(); }
	function extension() { return array_pop(explode('.', $this->id())); }
	function alt() { return '[IMG]'; }
	function author_name() { return NULL; }
	function image_type() { return NULL; }
	function file_name() { return array_pop(explode('/', $this->id())); }

	function _image_data()
	{
		if($this->__havefc())
			return $this->__lastc();

		$x = @getimagesize($this->file_name_with_path());
		if(!$x)
			$x = @getimagesize($this->url());

		$data = array();

		if(empty($x[0]))
			return $this->__setc(false);

		$data['width'] = intval($x[0]);
		$data['height'] = intval($x[1]);
//		$data['size'] = intval(@filesize($this->file_name_with_path()));
		$data['mime'] = $x['mime'];
		$data['ext'] = preg_replace('!^.+\.([^\.]+)$!', '$1', $this->file_name());
		return $this->__setc($data);
	}

	function width() { return ($data = $this->_image_data()) ? $data['width'] : false; }
	function height() { return ($data = $this->_image_data()) ? $data['height'] : false; }

	function wxh()
	{
		if($this->width() == 0 || $this->height() == 0)

		$w = $this->width() ? "width=\"{$this->width()}\"" : "";
		$h = $this->height() ? "height=\"{$this->height()}\"" : "";

		return  "{$h} {$w} alt=\"{$this->alt()}\"";
	}

	function html($args = array())
	{
		$append = defval($args, 'append');
		return "<img src=\"{$this->full_url()}\" {$this->wxh()} $append />";
	}

	static function load($path)
	{
		return bors_load(__CLASS__, $path);
	}
}
