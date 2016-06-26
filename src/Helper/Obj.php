<?php

namespace B2\Helper;

class Obj
{
	var $obj;
	function __construct($obj)
	{
		$this->obj = $obj;
	}

	function message($text, $params = [])
	{
		if(empty($params['theme_class']))
			$params['theme_class'] = $this->obj->get('theme_class');
//		if(empty($params['theme_class']) && empty($params['template']))
//			$params['template'] = $this->obj->get('template');

		return bors_message($text, $params);
	}
}
