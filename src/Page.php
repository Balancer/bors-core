<?php

namespace B2;

class Page extends \bors_page
{
//	use Smart;

	static function factory($id = NULL)
	{
		$class = get_called_class();
		$page = new $class($id);
		$page->configure();
		return $page;
	}

	function response()
	{
//		$response = new \Slim\Http\Response();
		$response = new \Zend\Diactoros\Response();

		$content = $this->content();

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
