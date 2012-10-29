<?php

function objects_property_empty($objects, $property)
{
	foreach($objects as $x)
		if($x->get($property))
			return false;

	return true;
}
