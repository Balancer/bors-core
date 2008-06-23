<?php

class bors_users_tools_favorites extends base_page
{
	function access() { return $this; }
	function can_action($action) { return bors()->user() && in_array($action, array('add', 'remove')); }

	function on_action_add($data)
	{
		$object = object_load($data['object']);
		if(!$object)
			return bors_message(ec('Не могу найти объект ').$data['object']);
		
		bors()->user()->favorite_add($object);
		return go_ref($data['ref']);
	}

	function on_action_remove($data)
	{
		$object = object_load($data['object']);
		if(!$object)
			return bors_message(ec('Не могу найти объект ').$data['object']);
		
		bors()->user()->favorite_remove($object);
		return go_ref($data['ref']);
	}
}
