<?php

function debug_hidden_log($type, $message=NULL, $trace = true, $args = [])
{
	bors_debug::syslog($type, $message, $trace, $args);
	bors_debug::syslog("notice/obsolete", "Call obsolete function debug_hidden_log");
}
