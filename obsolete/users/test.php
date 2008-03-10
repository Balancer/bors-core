<?
    require_once('funcs/DataBase.php');
    require_once('funcs/security.php');
    require_once('funcs/global-data.php');

    function user_data($key,$user=NULL,$def='')
	{
		
        if(is_global_key("user_data($user)",$key))
            return global_key("user_data($user)",$key);

        return set_global_key("user_data($user)",$key, _user_data($key, $user, $def));
	}
	
    function _user_data($key,$user,$def)
    {
		if($user == NULL)
		{
			$user = @$_POST['login'];
			if(!$user)
				$user = @$_COOKIE['login'];
			$_COOKIE['login'] = $user;
//			SetCookie("login", $user, time()+86400*7,"/", $_SERVER['HTTP_HOST']);
		}

		$data['Balancer'] = array(
			'password' => 'test',
			'nick' => 'Balancer',
			'level' => 999,
			'id' => 'Balancer',
		);

		$data['admin'] = array(
			'password' => 'admin}3_4',
			'nick' => 'Administrator',
			'level' => 10,
			'id' => 'admin',
		);

		if(!empty($data[$user]) && !empty($data[$user][$key]))
			return $data[$user][$key];
		else
			return $def;

    }

    function set_user_data($key, $value, $user=NULL)
    {

    }

    function check_password()
    {
        $member_id = @$_COOKIE['member_id'];

        if(!$member_id)
        {
            $nick = user_data('nick');
            echo "<h3><span style=\"text-color: red;\">Not logged or invalid user!";
            die();
        }
	}
	
    function access_allowed($page, $hts=NULL)
    {
        if(empty($hts))
            $hts = new DataBaseHTS;

        $base_page_access = $hts->base_value('default_access_level', 3);
        $ul = user_data('level',NULL,1);

        $pl = $hts->get_data($page, 'access_level', $base_page_access, true);
        return $ul >= $pl;
    }

    function access_warn($page, $hts=NULL)
    {
        if(empty($hts))
            $hts = new DataBaseHTS;

        $base_page_access = $hts->base_value('default_access_level', 3);
        $ul = user_data('level', NULL, 1);

//        echo "access_check: $base_page_access/$ul";

        $pl = $hts->get_data($page, 'access_level', $base_page_access, true);
        if($ul < $pl)
		{
            echo "<span style=\"color: red; font-weight: bold;\">Attention! Your accesslevel ($ul) lower then needed ($pl) to save changes! Changes will not saved!</span>";    
			return true;
		}
		return false;
    }

    function check_access($pl, $hts=NULL, $def=1)
    {   
//        check_password();

        // Если первый параметр число - уровень доступа пользователя должен быть не ниже его.
        // Если указано не число - то этот параметр считается страницей, с которой и считывается требуемый уровень доступа.
        // третий опциональный параметр - уровень доступа пользователя по умолчанию.

        if(!preg_match("!^\d+$!", $pl))
        {
            if(!$hts)
                $hts = new DataBaseHTS;
            $base_page_access = $hts->base_value('default_access_level', 3);
            $pl = $hts->get_data($pl, 'access_level', $base_page_access, true);
        }

        $ul = intval(user_data('level', NULL, $def));

//        echo("pl=$pl, ul=$ul, def=$def");

        if($ul<$pl)
        {
            $nick=user_data('nick');
            echo "<b><font color=\"red\">Accesslevel to user $nick ($ul) too low to this operation (need $pl)!</font></h3>";
            die();
        }
    }

    class User
    {
    	var $id;

		function User($_login = NULL)
		{
			$this->id = user_data('id', $_login);
		}
    	
    	function get($data, $default=NULL)
    	{
    		return user_data($data, $this->id, $default);
    	}

    	function data($data, $default=NULL)
    	{
    		return user_data($data, $this->id, $default);
    	}

		function set_data($key, $value)
		{
			set_user_data($key, $value, $this->id);
		}

	    function do_login($user, $password, $show_success=true)
    	{
        	$this->id = user_data('id', $user);
			
			if(!$this->id)
				return "<b>Unknown user '$user'</b>'";

			$pw = user_data('password', $user);

//			echo "pw=$password, pw=$pw, md=".md5($password).", lp=$lp;";
			
			if($password != $pw)
			{
				$this->do_logout();
				return "<b>Wrong password for user '$user'</b>'";
			}
			
			SetCookie("login", 		$user, time()+2592000,"/", $_SERVER['HTTP_HOST']);
			SetCookie("password",	$pw, time()+2592000,"/", $_SERVER['HTTP_HOST']);
			
			if($show_success)
				echo "<b>Вы успешно вошли в систему!</b>";

			return "";
		}

		function do_logout()
		{
			SetCookie("login","",0,"/");
			SetCookie("password","",0,"/");
			$_COOKIE['login'] = "";
			$_COOKIE['password'] = "";
		}
		
		function get_page()
		{
			return $GLOBALS['cms']['main_host_uri'] . "/users/~".$this->id."/";
		}
		
		function check_access($uri)
		{
			if(!$this->id)
			{
				$ret['title'] = "Ошибка входа";
				$ret['source'] = 'Вы не зашли в систему.';

				return $ret;
			}

			if(!access_allowed($uri))
			{
				$ret['title'] = "Ошибка доступа";
				$ret['source'] = 'У Вас недостаточно прав для выполнения операции';

				return $ret;
			}
			
			return NULL;
		}
	}
?>
