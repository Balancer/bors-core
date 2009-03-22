<?php

function bors_user_reputation_weight($value)
{
	$ret = (atan($value/5)*2/pi() + 1)/2;
	return $ret*$ret;
//	return (atan($value)*2/pi() + 1)/2;
}

function bors_user_reputation_weight_signed($value)
{
	$ret = atan($value/20)*2/pi();
	return $ret;
}
