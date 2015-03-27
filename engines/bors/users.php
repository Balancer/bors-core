<?php

function bors_user($id)
{
	return object_load('bors_user', $id);
}

if($user_class = config('user_class'))
{
	if(class_exists($user_class))
		bors_throw("Unknown user class '$user_class'");

	eval("class bors_user extends {$user_class} { function extends_class_name() { return '$user_class'; } }");
}
