<?php

class bors_admin_access extends bors_access
{
	function _can_list_def() { return $this->can_read(); }
	function can_read() { return $this->can_edit(); }
	function can_edit() { return $this->can_delete(); }
	function can_delete() { return $this->can_action(); }
	function can_action() { return false; }
}
