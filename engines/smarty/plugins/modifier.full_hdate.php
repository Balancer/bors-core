<?php

function smarty_modifier_full_hdate($time)
{
	include_once("inc/datetime.php");
   	return full_hdate(intval($time));
}
