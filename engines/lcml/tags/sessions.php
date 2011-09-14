<?php
    function lt_session($params)
    {
		__session_init();

		foreach(explode(' ', $params['orig']) as $var)
			session_register($var);

		return "";
    }

    function lt_session_echo($params)
    {
		if(!empty($_SESSION[$params['orig']]))
			return $_SESSION[$params['orig']];
		else
			return "";
    }
