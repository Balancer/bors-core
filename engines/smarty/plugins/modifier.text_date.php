<?php
	function smarty_modifier_text_date($time)
	{
		include_once("funcs/datetime.php");
    	return text_date($time);
	}
