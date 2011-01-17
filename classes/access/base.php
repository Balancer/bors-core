<?php

class access_base extends base_empty
{
	function can_read() { return true; }
	function can_edit() { return false; }
	function can_new() { return $this->can_edit(); }

	function can_action()
	{
		$me = bors()->user();
		if(!$me)
			return false;

		// Если ID объекта уже есть, то это - редактирование старого объекта, иначе - создание нового.
		if($this->id()->id())
			return $this->can_edit() || $me->can_edit($this->id());
		else
			return $this->can_new();
	}
}
