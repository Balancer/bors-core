<?php

class auto_object_php extends base_object
{
	function content()
	{
		$data = url_parse($this->id());
		$object = object_load('auto_php_'.str_replace('/', '_', trim($data['path'], '/')), $this->id());
		if($object)
		{
			bors()->set_main_object($object);
			if(!$object->parents(true))
				$object->set_parents(array(dirname($data['path'])));
			return $object->content();
		}
		
		return false;
	}
}
