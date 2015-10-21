<?php

namespace B2;

class Null extends Object
{
	function __call($method, $params)
	{
		return $this;
	}

	function __toString() { return ''; }
}
