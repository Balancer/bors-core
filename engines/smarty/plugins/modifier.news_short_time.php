<?php

function smarty_modifier_news_short_time($time)
{
	require_once BORS_CORE.'/inc/datetime.php';
   	return news_short_time($time);
}
