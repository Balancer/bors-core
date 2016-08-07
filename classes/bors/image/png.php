<?php

class bors_image_png extends bors_object
{
	function can_be_empty() { return true; }

	function render_engine() { return __CLASS__; }

	function render($object)
	{
		$image = $object->image(); // Высчитываем картинку до передачи типа, чтобы видеть ошибки
		header("Content-type: " . image_type_to_mime_type(IMAGETYPE_PNG));
		return $image;
	}

	function image()
	{
		ob_start();
		$this->show_image();
		$png = ob_get_contents();
		ob_end_clean();

		return $png;
	}

	function response()
	{
		$response = new \Zend\Diactoros\Response();

		$content = $this->content();

		\bors_debug::append_info($content, $this);

		if(!$content)
			return NULL;

		if($content === true)
			return NULL;

		foreach($this->headers() as $name => $value)
			$response = $response->withHeader($name, $value);

		$response->getBody()->write($content);

		return $response;
	}
}
