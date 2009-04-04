<?php

class user_cookieHashUpdate extends base_object
{
	function pre_parse()
	{
		$cookie_hash = $this->id();
		$user_id = bors_user::id_by_cookie($cookie_hash);
		if($user = object_load('bors_user', $user_id))
			$user->cookie_hash_set(-1, false);
		
		return true;
	}
}
