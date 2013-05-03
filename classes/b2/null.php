<?php

class b2_null extends bors_object_empty
{
	function __call($method, $params)
	{
		return $this;
	}

	function __toString() { return ''; }
}
