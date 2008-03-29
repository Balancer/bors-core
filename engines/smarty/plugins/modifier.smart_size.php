<?php

function smarty_modifier_smart_size($size)
{
	include_once("inc/filesystem.php");
   	return smart_size($size);
}
