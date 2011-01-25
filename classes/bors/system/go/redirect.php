<?php

class bors_system_go_redirect extends base_object
{
	function pre_show()
	{
		$object = NULL;

		if(preg_match('/^(\w)(\d+)$/', $this->id(), $m))
		{
			switch($m[1])
			{
				case 'p':
					$object = bors_load('balancer_board_post', $m[2]);
					break;
			}
		}

		if($object)
			return go($object->url_in_container(), true);

		return false;
	}
}
