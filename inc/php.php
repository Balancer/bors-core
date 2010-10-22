<?php

function bors_php_fetch($code)
{
	ob_start();
	eval($code);
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}
