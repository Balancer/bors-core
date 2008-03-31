<?php

class common_date_minutes extends base_null
{
	function id() { return 0; }
	function page() { return 1; }

	function minutes_list()
	{
		$res = array(0=>'--');

		for($d=1; $d<=60; $d++)
			$res[$d] = $d;

		return $res;
	}

	function minutes_list_cur()
	{
		$res = array(0=>'--');

		for($d=1; $d<=60; $d++)
			$res[$d] = $d;

		return $res;
	}
}
