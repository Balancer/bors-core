<?php

class bors_json extends bors_object
{
	function pre_show()
	{
		header("Content-type: application/json; charset=".config('output_charset'));
		config_set('debug.timing', false); // Чтобы не мусорить комментарием в конце JSON.
		echo json_encode($this->data(), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		return true;
	}
}
