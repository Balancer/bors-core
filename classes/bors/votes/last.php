<?php

class bors_votes_last extends base_page
{
	function title() { return ec('Оценки сообщений'); }
	function config_class() { return 'airbase_board_config'; }

	function local_data()
	{
		return array(
			'last' => objects_array('bors_votes_thumb', array(
				'order' => '-create_time',
				'limit' => 25,
			)),
		);
	}

	function pre_show()
	{
		templates_noindex();

		if(bors()->client()->is_bot())
			return go('/');

		return false;
	}

	function cache_static() { return rand(60, 120); }
}
