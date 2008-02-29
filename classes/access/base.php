<?php

class access_base extends base_empty
{
	function can_read() { return true; }
	function can_edit() { return false; }
	function can_new() { return $this->can_edit(); }

	function can_action()
	{
		// Если ID объекта уже есть, то это - редактирование старого объекта, иначе - создание нового.
		if($this->id()->id())
			return $this->can_edit();
		else
			return $this->can_new();
	}
}
