<?php

function smarty_modifier_smart_size($size)
{
	include_once("funcs/filesystem_ext.php");
   	return smart_size($size);
}
