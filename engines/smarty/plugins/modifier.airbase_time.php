<?php
	function smarty_modifier_airbase_time($time)
	{
		include_once("funcs/datetime.php");
    	return airbase_time($time);
	}
