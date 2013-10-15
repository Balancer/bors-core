<?php

class bors_var
{
	static function get($name, $default = NULL)
	{
		$x = bors_find_first('bors_var_db', array('name' => $name));
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
		$x = bors_find_first('bors_var_db', array('name' => $name));

		if(!$x)
			return NULL;

		if($x->expire_time() > 0 && $x->expire_time() <= time())
		{
			$x->delete();
			return NULL;
		}

		return $x;
	}

	static function set($name, $value, $time_to_live = NULL)
	{
		$x = bors_new('bors_var_db', array(
			'name' => $name,
			'value' => $value,
			'expire_time' => $time_to_live > 0 ? time() + $time_to_live : $time_to_live,
		));

		return $value;
	}

	static function edit_html_code($name, $title = NULL)
	{
		$url = "/_bors/admin/edit-var?var={$name}";

		if($title)
			$title = "$title&nbsp;";
		else
			$title = "";

		return "<a href=\"$url\"
			onclick=\"popupWin = window.open('$url&is_popup=1', 'edit', 'width=600,height=400,top=0'); popupWin.focus(); return false;\">$title<img src=\"/_bors/i16/edit.png\" style=\"vertical-align: middle\" /></a>";
	}

	static function fast_get($var_name, $default = NULL)
	{
		$ch = new bors_cache_fast();
		return $ch->get('bors-var', $var_name, $default);
	}

	static function fast_set($var_name, $value, $ttl = 86400)
	{
		$ch = new bors_cache_fast();
		$ch->get('bors-var', $var_name);
		return $ch->set($value, $ttl);
	}
}
