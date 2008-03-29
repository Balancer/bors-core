<?php
	function smarty_modifier_month_name($month)
	{
		include_once("inc/datetime.php");
    	return month_name($month);
	}
