<?php

class bors_access_flat extends access_base
{
	function can_action() { return ($me = bors()->user()) && $me->is_admin(); }
}
