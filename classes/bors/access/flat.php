<?php

class bors_access_flat extends access_base
{
	function can_edit() { return $this->can_action(); }

	function can_action()
	{
		$me = bors()->user();
		return $me && $me->is_admin();
	}
}
