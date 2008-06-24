<?php

class bors_users_tools_subscribe extends base_page
{
	function access() { return $this; }
	function can_action($action) { return in_array($action, array('add', 'remove')); }
	function can_read() { return true; }

	private function check_register()
	{
		if(bors()->user())
			return true;
			
		bors_message(ec('Извините, эта функция доступна только зарегистрированным пользователям сайта'));
		return false;
	}

	function on_action_add($data)
	{
		if(!$this->check_register())
			return true;
	
		$object = object_load($data['object']);
		if(!$object)
			return bors_message(ec('Не могу найти объект ').$data['object']);
		
		bors()->user()->subscribe_add($object);
		return go_ref(@$data['ref']);
	}

	function on_action_remove($data)
	{
		if(!$this->check_register())
			return true;
			
		$object = object_load($data['object']);
		if(!$object)
			return bors_message(ec('Не могу найти объект ').$data['object']);
		
		bors()->user()->subscribe_remove($object);
		return go_ref(@$data['ref']);
	}
}
