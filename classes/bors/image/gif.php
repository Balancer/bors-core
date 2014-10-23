<?php

class bors_image_gif extends bors_object
{
	function can_be_empty() { return true; }

	function render_engine() { return 'bors_image_gif'; }

	function render($object)
	{
		header("Content-type: " . image_type_to_mime_type(IMAGETYPE_GIF));
		return $object->image();
	}

	function image()
	{
		ob_start();
		$this->show_image();
		$gif = ob_get_contents();
		ob_end_clean();

		return $gif;
	}
}
