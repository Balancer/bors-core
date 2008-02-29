<?php
	function smarty_modifier_full_time($time)
	{
		include_once("funcs/datetime.php");
    	return full_time($time);
	}

?>
