<?php

class bors_client extends base_object
{
	function can_cached() { return false; }
	function is_bot() { return @$GLOBALS['client']['is_bot']; }
}
