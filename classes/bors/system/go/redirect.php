<?php

class bors_system_go_redirect extends base_object
{
	function title() { return object_property($this->object(), 'title'); }

	function pre_show()
	{
		if($object = $this->object())
			return go($object->url_in_container(), true);

		return false;
	}

	function object()
	{
		$object = NULL;

		if(preg_match('/^(\w)(\d+)$/', $this->id(), $m))
		{
			switch($m[1])
			{
				case 'p':
					$object = bors_load('balancer_board_post', $m[2]);
					break;
				case 't':
					$object = bors_load('balancer_board_topic', $m[2]);
					break;
			}
		}

		return $object;
	}
}
