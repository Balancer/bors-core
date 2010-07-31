<?php

class bors_referer_main extends base_page
{
	function title() { return ec('Внешние ссылки'); }
	function config_class() { return 'airbase_board_config'; }

	function local_data()
	{
		return array(
			'searches' => objects_array('bors_referer_search', array(
				'target_class_name NOT IN' => array('bors_image_autothumb'),
				'order' => '-count',
				'limit' => 25,
			)),
			'links' => objects_array('bors_referer_link', array(
				'target_class_name NOT IN' => array('bors_image_autothumb'),
				'order' => '-count',
				'limit' => 25,
			)),
		);
	}

	function pre_show()
	{
		template_noindex();

		if(bors()->client()->is_bot())
			return go('/');

		return false;
	}
}
