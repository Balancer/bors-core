<?php

class bors_templates_php
{
	static function fetch($template, $data)
	{
		ob_start();
		$err_rep_save = error_reporting();
		error_reporting($err_rep_save & ~E_NOTICE);
		extract($data);
		require($template);
		error_reporting($err_rep_save);
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}
}
