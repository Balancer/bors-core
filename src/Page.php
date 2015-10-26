<?php

namespace B2;

class Page extends View
{
//	use Smart;

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
