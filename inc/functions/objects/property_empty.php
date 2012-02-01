<?php

function objects_property_empty(Array $objects, String $property)
{
	foreach($objects as $x)
		if($x->get($property))
			return false;

	return true;
}
