<?php

function bors_concat_bl()
{
	$args = array_filter(func_get_args());
	return join(' ', array_filter($args));
}
