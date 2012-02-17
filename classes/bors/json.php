<?php

class bors_json extends bors_object
{
	function pre_show()
	{
		header("Content-type: application/json");
		config_set('debug.timing', false); // Чтобы не мусорить комментарием в конце JSON.
		echo json_encode($this->data());
		return true;
	}
}
