<?php

class bors_json extends bors_page
{
	function pre_show()
	{
		$return = parent::pre_show();;

		header("Content-type: application/json; charset=".\B2\Cfg::get('output_charset'));
		config_set('debug.timing', false); // Чтобы не мусорить комментарием в конце JSON.

		return $return;
	}

	function direct_content()
	{
		if(version_compare(PHP_VERSION, '5.4.0') >= 0)
			return json_encode($this->data(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		else
			return json_encode($this->data()); // JSON_NUMERIC_CHECK — убираем пока, а то бывают проблемы с Select2
	}
}
