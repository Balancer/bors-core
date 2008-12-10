<?php

class bors_admin_edit extends base_page
{
	private $object = false;
	function object()
	{
		if($this->object === false)
			$this->object = object_load($_GET['object']);
			
		return $this->object;
	}

	function data_providers()
	{
		return array(
			'object' => $this->object(),
		);
	}
	
	function parents() { return array($this->object()); }
	function title() { return ec('Редактор объекта ').$this->object()->title(); }
	function nav_name() { return ec('редактор'); }

//	function url() { return '/admin/edit/?object='.$this->object()->internal_uri(); }

	function obj_source() { return $this->object()->source(); }
}
