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

	function local_template_data_set()
	{
		return array(
			'object' => $this->object(),
		);
	}
	
	function pre_show()
	{
		if(!$this->object())
			return bors_message(ec('Неизвестный объект '.@$_GET['object']));
		
		return false;
	}
	
	function parents() { return array($this->object()); }
	function title() { return ec('Редактор объекта ').$this->object()->title(); }
	function nav_name() { return ec('редактор'); }

//	function url() { return '/admin/edit/?object='.$this->object()->internal_uri(); }

	function obj_source() { return $this->object()->source(); }
}
