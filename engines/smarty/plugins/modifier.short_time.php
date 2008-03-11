<?php
	function smarty_modifier_short_time($time)
	{
		include_once("inc/datetime.php");
    	return short_time($time);
	}

?>
