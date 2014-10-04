<?php

class examples_topReputation extends base_page
{
	function title() { return ec("Наш лучший пользователь"); }
	function body_data()
	{
		return array(
			'user' => bors_find_first('balancer_board_user', array('order' => '-reputation')),
		);
	}
}
