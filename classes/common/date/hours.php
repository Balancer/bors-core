<?php

class common_date_hours extends base_null
{
	function id() { return 0; }
	function page() { return 1; }

	function hours_list()
	{
		$res = array(0=>'--');

		for($d=1; $d<=24; $d++)
			$res[$d] = $d;

		return $res;
	}

	function hours_list_cur()
	{
		$res = array(0=>'--');

		for($d=1; $d<=24; $d++)
			$res[$d] = $d;

		return $res;
	}
}
