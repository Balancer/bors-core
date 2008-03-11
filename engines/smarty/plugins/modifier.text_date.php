<?php
	function smarty_modifier_text_date($time)
	{
		include_once("inc/datetime.php");
    	return text_date($time);
	}
