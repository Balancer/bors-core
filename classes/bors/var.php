<?php

class bors_var
{
	static function get($name, $default = NULL)
	{
		$x = objects_first('bors_var_db', array('name' => $name));
		if(!$x)
			return $default;

		if($x->expire_time() >= 0 && $x->expire_time() <= time())
		{
			$x->delete();
			return $default;
		}

		return $x->value();
	}

	static function set($name, $value, $time_to_live)
	{
		$x = object_new_instance('bors_var_db', array(
			'name' => $name,
			'value' => $value,
			'expire_time' => $time_to_live > 0 ? time() + $time_to_live : $time_to_live,
		));
		$x->store();

		return $value;
	}
}
