<?php

class bors_json extends bors_object
{
	function pre_show()
	{
		header("Content-type: application/json; charset=".config('output_charset'));
		config_set('debug.timing', false); // Чтобы не мусорить комментарием в конце JSON.
		if(version_compare(PHP_VERSION, '5.4.0') >= 0)
			echo json_encode($this->data(), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		else
			echo json_encode($this->data(), JSON_NUMERIC_CHECK);
		return true;
	}
}
