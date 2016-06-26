<?php

namespace B2\Traits;

trait View
{
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
