<?php

class base_image_svg extends base_object
{
	function can_be_empty() { return true; }

	function render_engine() { return 'base_image_svg'; }

	function render($object)
	{
		$image = $object->image(); // Высчитываем картинку до передачи типа, чтобы видеть ошибки
//		header("Content-type: image/svg+xml");
		return $image;
	}

	function image()
	{
		ob_start();
		$this->show_image();
		$svg = ob_get_contents();
		ob_end_clean();
		
		return $svg;
	}
}
