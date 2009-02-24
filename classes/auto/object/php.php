<?php

class auto_object_php extends base_object
{
	function init() { }
	function loaded() { return $this->object(); }
	function can_be_empty() { return false; }

	private $object = false;
	function object()
	{
		if($this->object !== false)
			return $this->object;
		
		$data = url_parse($this->id());
		$object = $this->object = object_load('auto_php_'.str_replace('/', '_', trim($data['path'], '/')), $this->id());

		if($object)
		{
			bors()->set_main_object($object);
			if(!$object->parents(true))
				$object->set_parents(array(dirname($data['path'])));
		}
		
		return $object;
	}

	function content()
	{
		return $this->object()->content();
	}
}
