<?php

function is_today($time)
{
	global $now;
	if($now - $time < 86400 && strftime("%d", $time) == strftime("%d", $now))
		return true;

	//FIXME: разобраться, wtf?
	if(preg_match("!\d{4}/\d{1,2}/\d{1,2}/$!", @$GLOBALS['main_uri']))
		return true;

	return false;
}
