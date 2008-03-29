<?php
	function smarty_modifier_news_short_time($time)
	{
		include_once('inc/datetime.php');
    	return news_short_time($time);
	}
