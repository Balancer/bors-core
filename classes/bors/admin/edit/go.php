<?php

class bors_admin_edit_go extends bors_admin_page
{
	function _configure() { } // Ничего не делаем
	function init() { } // Ничего не делаем
	function url() { return NULL; }

	function pre_show()
	{
		$target = bors_load_uri($this->id());
		return go($target->admin()->admin_url(), true);
	}

	function access() { return $this; }
	function can_read() { return true; }
}
