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
}
