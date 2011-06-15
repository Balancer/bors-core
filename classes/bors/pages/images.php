<?php

class bors_pages_images extends bors_page
{
	function _readme()
	{
		if($this->__havefc())
			return $this->__lastc();

		$text = ec(@file_get_contents($this->dir().'/README.MD'));
		$md = bors_markup_markdown::factory($text);
		return $this->__setc($md);
	}

	function title()
	{
		return object_property($this->_readme(), 'title', ec('Изображения'));
	}

	function body_data()
	{
		$files = search_dir($this->dir(), '\.(png|jpg|jpeg|gif$)');

		rsort($files);

		$images = array();

		foreach($files as $full_file_name)
		{
			$file = basename($full_file_name);
			$img = objects_first('bors_image', array('full_file_name' => $full_file_name));
			if(!$img)
				$img = aviaport_image::register_file($full_file_name);

			$img->set_attr('filemtime', filemtime($full_file_name));
			$images[] = $img;
		}

		return compact('images');
	}
}
