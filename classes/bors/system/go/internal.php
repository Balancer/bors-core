<?php

class bors_system_go_internal extends base_object
{
	function pre_show()
	{
		$object = object_load($this->id());
//		echo "{$this->id()} => $object";
		return $object->igo();
	}

	function target() { return object_load($this->id()); }

	function title() { return $this->target()->title(); }
}
