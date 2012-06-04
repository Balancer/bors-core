<?php

class access_full extends base_empty
{
	function _can_list_def() { return $this->can_read(); }
	function can_read() { return true; }
	function can_action() { return true; }
}
