<?php

class bors_admin_edit_page extends bors_admin_base_page
{
	function title() { return ec('редактор'); }

	function local_data()
	{
		templates_noindex();

		return array(
			'object' => $this->object(),
			'referer' => ($ref = bors()->referer()) ? $ref : 'newpage_admin',
		);
	}
}
