<?php

class bors_admin_links_config extends bors_admin_config
{
	function template_data()
	{
		$page_tabs = array(
			'/_bors/admin/links/' => ec('Все связи'),
			'/_bors/admin/links/search/' => ec('Поиск'),
		);

		return array_merge(parent::template_data(), compact('page_tabs'));
	}
}
