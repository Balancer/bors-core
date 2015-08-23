<?php

class access_full extends bors_object_simple
{
	function _can_list_def() { return $this->can_read(); }
	function can_read() { return true; }
	function can_action($action, $data) { return true; }
}
