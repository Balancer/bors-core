<?php

function bors_concat()
{
	$args = array_filter(func_get_args());
	$delimiter = array_shift($args);
	return join($delimiter, array_filter($args));
}
