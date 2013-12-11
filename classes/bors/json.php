<?php

class bors_json extends bors_object
{
	function pre_show()
	{
		header("Content-type: application/json");
		config_set('debug.timing', false); // Чтобы не мусорить комментарием в конце JSON.
		if($this->get('use_53'))
			echo json_encode($this->data(), JSON_NUMERIC_CHECK);
		else
			echo json_encode($this->data());
		return true;
	}
}
