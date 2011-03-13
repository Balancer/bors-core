<?php

class bors_image_file extends bors_object
{
	function thumbnail_class() { return 'bors_image_thumbnail'; }

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
}
