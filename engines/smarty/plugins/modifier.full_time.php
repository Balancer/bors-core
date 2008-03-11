<?php
	function smarty_modifier_full_time($time)
	{
		include_once("inc/datetime.php");
    	return full_time($time);
	}

?>
