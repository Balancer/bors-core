<?php

class base_image_png extends base_object
{
	function can_be_empty() { return true; }

	function render_engine() { return 'base_image_png'; }

	function render($object)
	{
		header("Content-type: " . image_type_to_mime_type(IMAGETYPE_PNG));
		return $object->image();
	}

	function image()
	{
		ob_start();
		$this->show_image();
		$png = ob_get_contents();
		ob_end_clean();
		
		return $png;
	}
}
