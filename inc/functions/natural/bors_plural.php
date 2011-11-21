<?php

function bors_plural($s)
{
	if(preg_match('/s$/', $s))
		return $s.'es';

	if(preg_match('/y$/', $s))
		return substr($s,0,bors_strlen($s)-1).'ies';

	return $s.'s';
}
