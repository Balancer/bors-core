<?php

class examples_topReputation extends base_page
{
	function title() { return ec("Наш лучший пользователь"); }
	function local_data()
	{
		return array(
			'user' => objects_first('forum_user', array('order' => '-reputation')),
		);
	}
}
										
											