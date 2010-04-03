<?php
	function smarty_modifier_short_time($time, $def = '')
	{
		include_once("inc/datetime.php");
    	return short_time($time, $def);
	}
