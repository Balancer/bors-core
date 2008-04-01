<?php

class base_image_png extends base_object
{
	function can_be_empty() { return true; }

	function render_engine() { return 'base_image_gif'; }

	function render($object)
	{
		header("Content-type: " . image_type_to_mime_type(IMAGETYPE_GIF));
		return $object->image();
	}
}
