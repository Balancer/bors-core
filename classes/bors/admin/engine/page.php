<?php

class bors_admin_engine_page extends bors_admin_engine
{
	function edit_url()
	{
		return '/_bors/admin/edit/page?object='.urlencode($this->object()->internal_uri());
	}
}
