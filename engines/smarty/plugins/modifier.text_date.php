<?php
	function smarty_modifier_text_date($time)
	{
		require_once BORS_CORE.'/inc/datetime.php';
    	return text_date($time);
	}
