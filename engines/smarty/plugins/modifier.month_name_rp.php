<?php
	function smarty_modifier_month_name_rp($month)
	{
		include_once("inc/datetime.php");
    	return month_name_rp($month);
	}
