<?
    function lt_session($params)
    {
		session_start();
		foreach(split(' ', $params['orig']) as $var)
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
