<?php

class access_base extends base_empty
{
	function object() { return $this->id(); }

	function can_read() { return true; }
	function can_edit() { return false; }
	function can_new() { return $this->can_edit(); }

	function can_action()
	{
		$me = bors()->user();
		if(!$me)
			return false;

		// Если ID объекта уже есть, то это - редактирование старого объекта, иначе - создание нового.
		if($this->object()->id())
		{
			if($this->can_edit())
				return true;
			if(method_exists($me, 'can_edit_object'))
				return $me->can_edit_object($this->object());

			return false;
		}
		else
			return $this->can_new();
	}
}
