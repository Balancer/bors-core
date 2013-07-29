<?php

class bors_system_go_internal extends base_object
{
	function pre_show()
	{
		if($object = object_load($this->id()))
			return $object->igo();

		return bors_http_error(404);
	}

	function target() { return object_load($this->id()); }

	function title() { return $this->target()->title(); }
}
