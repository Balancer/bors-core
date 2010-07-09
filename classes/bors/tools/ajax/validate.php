<?php

class bors_tools_ajax_validate extends base_page
{
	function pre_show()
	{
//		debug_hidden_log('test', print_r($_POST, true));
		/* RECEIVE VALUE */
		$validateValue = $_POST['validateValue'];
		$validateId = $_POST['validateId'];
		$validateError = $_POST['validateError'];

		/* RETURN VALUE */
		$arrayToJs = array();
		$arrayToJs[0] = $validateId;
		$arrayToJs[1] = $validateError;

		switch($validateError)
		{
			case 'loginFree':
				if(objects_first('aviaport_user', array('login' => $validateValue)))
					$arrayToJs[2] = "false";
				else
					$arrayToJs[2] = "true";
				break;

			case 'emailNotRegistered':
				if(objects_first('aviaport_user', array('email' => $validateValue)))
					$arrayToJs[2] = "false";
				else
					$arrayToJs[2] = "true";
				break;

			default:
				$arrayToJs[2] = "false";
				break;
		}

		echo '{"jsonValidateReturn":'.json_encode($arrayToJs).'}';

		return true;
	}
}
