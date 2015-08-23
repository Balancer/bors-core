<?php

class bors_access_flat extends access_base
{
	function can_edit() { return $this->can_action(NULL,NULL); }

	function can_action($action, $data)
	{
		$me = bors()->user();
		return $me && $me->is_admin();
	}
}
