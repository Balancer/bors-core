<?php

class bors_ajax extends bors_object
{
	function pre_show()
	{
		$data = $this->body_data();
		if(is_array($data))
		{
			header('Content-type: application/json; charset: utf-8');
			echo json_encode($data);
		}
		else
		{
			header('Content-Type: text/html; charset: utf-8');
			echo $data;
		}

		return true;
	}
}
