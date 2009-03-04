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
		$path = $data['path'];
		$page = 1;
		if(preg_match('!^(.+/)(\d+)\.html$!', $path, $m))
		{
			$path = $m[1];
			$page = $m[2];
		}
		
		$object = $this->object = object_load('auto_php_'.str_replace('/', '_', trim($path, '/')), $this->id());

		if($object)
		{
			$object->set_page($page);
			$object->set_called_url($this->id());
			bors()->set_main_object($object);
			if(!$object->parents(true))
				$object->set_parents(array(dirname($data['path']).'/'));
		}
		
		return $object;
	}

	function content()
	{
		return $this->object()->content();
	}
}
