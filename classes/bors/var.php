<?php

class bors_var
{
	static function get($name, $default = NULL)
	{
		$x = objects_first('bors_var_db', array('name' => $name));
		if(!$x)
			return $default;

		if($x->expire_time() > 0 && $x->expire_time() <= time())
		{
			$x->delete();
			return $default;
		}

		return $x->value();
	}

	static function get_var($name)
	{
		$x = objects_first('bors_var_db', array('name' => $name));

		if(!$x)
			return NULL;

		if($x->expire_time() > 0 && $x->expire_time() <= time())
		{
			$x->delete();
			return NULL;
		}

		return $x;
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

	static function edit_html_code($name)
	{
		$url = "/_bors/admin/edit-var?var={$name}";
		return "<a href=\"$url\"
			onclick=\"popupWin = window.open('$url&is_popup=1', 'edit', 'width=600,height=400,top=0'); popupWin.focus(); return false;\"><img src=\"/_bors/i16/edit.png\" style=\"vertical-align: middle\" /></a>";
	}
}
