<?php

class bors_access_public extends bors_access
{
	function can_read() { return true; }
	function _can_list_def() { return $this->can_read(); }
}
