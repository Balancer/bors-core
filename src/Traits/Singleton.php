<?php

namespace B2\Traits;

trait Singleton
{
	var $_b2_singleton = NULL;
	function b2()
	{
		if(empty($this->_b2_singleton))
			$this->_b2_singleton = new \B2\Helper\Obj($this);

		return $this->_b2_singleton;
	}
}
