<?php
	function smarty_modifier_airbase_time($time)
	{
		include_once("inc/datetime.php");
    	return airbase_time($time);
	}
