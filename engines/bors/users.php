<?php

function bors_user($id)
{
	return object_load('bors_user', $id);
}

if($user_class = config('user_class'))
{
	ob_start();
	eval("class bors_user extends {$user_class} { function extends_class_name() { return '$user_class'; } }");
	if(($err = ob_get_clean()) != '')
		bors_throw("Error eval bors_user extends '$user_class': $err");
}
