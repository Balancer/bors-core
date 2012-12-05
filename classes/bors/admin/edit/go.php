<?php

class bors_admin_edit_go extends bors_admin_page
{
	function pre_show()
	{
		$target = bors_load_uri($this->id());
		return go($target->admin()->admin_url(), true);
	}
}
