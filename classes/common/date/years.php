<?php

class common_date_years extends base_list
{
	function years_list()
	{
		$res = array( 0 => '----');

		for($d = strftime('%Y', time())+1; $d >= 1900; $d--)
			$res[$d] = $d;

		return $res;
	}
}
