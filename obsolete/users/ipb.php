<?
    require_once('funcs/DataBase.php');
    require_once('funcs/security.php');
    require_once('funcs/global-data.php');

    $GLOBALS['forums_data']=array(
            'nick' => 'forums_airbase_ru ib_members name id',
            'name' => 'forums_airbase_ru ib_members name id',
            'password' => 'forums_airbase_ru ib_members member_login_key id',
            'reputation' => 'forums_airbase_ru ib_members rep id',
            'member_login_key' => 'forums_airbase_ru  ib_members member_login_key id',
            'converge_pass_hash' => 'forums_airbase_ru ib_members_converge converge_pass_hash converge_id',
            'joined' => 'forums_airbase_ru ib_members joined id',
            'email' => 'forums_airbase_ru ib_members email id',
			'salt' => 'forums_airbase_ru ib_members_converge converge_pass_salt converge_id',
        );

    function user_data($key,$user=NULL,$def='')
    {
        if(is_global_key("user_data($user)",$key))
            return global_key("user_data($user)",$key);

        if($key == 'member_id' && !$user)
        {
            return empty($_COOKIE['member_id']) ? 0 : $_COOKIE['member_id'];
        }

//        echo "Try get data for user '$user' (cookie = ".@$_COOKIE['member_id'].")for key '$key' (def=$def)<br>\n";

        if($user)
        {
            $member_id = $user;
        }
        else
        {
            if(!empty($_COOKIE['member_id']))
            {
                $member_id = intval($_COOKIE['member_id']);
            }
            else
            {
                if($key == 'nick' || $key == 'name')
                    return $def ? $def : get_ip_nick();
                else
                    return $def;//'Unknown(parameter error)';
            }
        }

        $db = new DataBase('USERS');
		
        if($user && !preg_match("!^\d+$!", $user))
            $member_id = $db->get("SELECT `id` FROM `forums_airbase_ru`.`ib_members` WHERE `name` LIKE '".addslashes($user)."'", false);
		
        $member_id = intval($member_id);

        if(!$member_id)
            return isset($def)?$def:false;

		if($key == 'member_id')
			return $member_id;

//        echo "Try get not cached data for user $member_id for key '$key'<br>\n";

        unset($value);

//        if(isset($funcs_data[$key]))
//        {
//            $value = $funcs_data[$key]($user,$def);
//        }
//        else

        global $forums_data;
        
        {
            if(!isset($forums_data[$key]) || !$forums_data[$key])
                $value = $db->get("SELECT `value` FROM `users_data` WHERE `member_id`=$member_id AND `key`='".addslashes($key)."'");
            else
			{
				list($base, $table, $field, $key) = split(" +",$forums_data[$key]);
                $value = $db->get("SELECT $field FROM $base.$table WHERE $key=$member_id");
        	}
		}
       
//        echo "val=$value<br>\n";

    if(0 && $key=='level' && $member_id==1)
    {
    	return 1;
    }

        return $value ? set_global_key("user_data($user)",$key, $value) : $def;
    }

    function set_user_data($key,$value,$user=NULL)
    {
        global $forums_data;
        $member_id = isset($_COOKIE['member_id']) ? intval($_COOKIE['member_id']) : 0;

        if(!$member_id)
            $member_id=intval($user);

        if(!$member_id)
            return false;

        $db = new DataBase('USERS');
        if(empty($forums_data[$key]))
            $db->store('users_data',"`member_id`=$member_id AND `key`='".addslashes($key)."'",array('member_id'=>$member_id,'key'=>$key,'value'=>$value));
        else
            die("UPDATE forums_airbase_ru.ib_members SET `".$forums_data[$key]."`='".addslashes($value)."'");
        return set_global_key('user_data',$member_id."_".$key,$value);
    }

    function check_password()
    {
        $member_id = !empty($_COOKIE['member_id']) ? intval($_COOKIE['member_id']) : 0;
        $password  = !empty($_COOKIE['pass_hash']) ? $_COOKIE['pass_hash'] : '';
//        echo "<br>".user_data('password')."<br>";

        if(!$password)
        {
            $nick = user_data('nick');
            echo "<h3><span style=\"text-color: red;\">Пароль пользователя $nick ($member_id) не может быть пустой!</span></h3>Залогиниться, зарегистрироваться или сменить аккаунт можно <a href=\"http://forums.airbase.ru/\">форуме Авиабазы</a>.<br><span style=\"font-size: xx-small;\">Внимание! Вместо старой системы регистрации теперь будет использоваться новая, объединённая с регистрацией на форумах!";
            die();
        }

        if($password!=user_data('password'))
        {
            $nick=user_data('nick');
            echo "<h3><span style=\"text-color: red;\">Ошибка пароля или логина пользователя $nick! ($member_id)</span></h3>Залогиниться, зарегистрироваться или сменить аккаунт можно <a href=\"http://forums.airbase.ru/\">форуме Авиабазы</a>.<br><span style=\"font-size: xx-small;\">Внимание! Вместо старой системы регистрации теперь будет использоваться новая, объединённая с регистрацией на форумах!";
            die();
        }
    }

    function user_data_array($key,$user=NULL,$def=array())
    {
        if(!$member_id = get_user($user)) return isset($def)?$def:false;

        if(is_global_key('user_data_array',$member_id.'_'.$key))
            return global_key('user_data_array',$member_id.'_'.$key);

        $db = new DataBase('USERS');
        $value = $db->get_array("SELECT `value` FROM `users_data` WHERE `member_id`=$member_id AND `key`='".addslashes($key)."'");

        if(!$value) $value=$def;

        return set_global_key('user_data_array',$member_id.'_'.$key,$value);
    }

    function set_user_data_array($key,$value,$user=NULL)
    {
        if(!$member_id = get_user($user)) return false;

        $db = new DataBase('USERS');

        $fields=array();
        foreach($value as $v)
        {
            array_push($fields,array('member_id'=>$member_id,'key'=>$key,'value'=>$v));
        }

        $db->store_array('users_data',"`member_id`=$member_id AND `key`='".addslashes($key)."'",$fields);
        return set_global_key('user_data',$member_id."_".$key,$value);
    }

    function get_user($user)
    {
        if($user) return intval($user);
        return isset($_COOKIE['member_id']) ? intval($_COOKIE['member_id']) : false;
    }

    function get_member_id_by_name($name)
    {
        $db = new DataBase('forums_airbase_ru');
        return intval($db->get("SELECT `id` FROM `ib_members` WHERE `name`='".addslashes($name)."'"));
    }

    function access_allowed($page, $hts=NULL)
    {
        if(empty($hts))
            $hts = new DataBaseHTS;

        $base_page_access = $hts->base_value('default_access_level', 3);
        $ul = intval(user_data('level',NULL,1));

        $pl = $hts->get_data($page, 'access_level', $base_page_access, true);
        return $ul >= $pl;
    }

    function access_warn($page, $hts=NULL)
    {
        if(empty($hts))
            $hts = new DataBaseHTS;
        $base_page_access = $hts->base_value('default_access_level', 3);
        $ul = intval(user_data('level',NULL,1));

//        echo "access_check: $base_page_access/$ul";

        $pl = $hts->get_data($page, 'access_level', $base_page_access, true);
        if($ul < $pl)
		{
            echo "<span style=\"color: red; font-weight: bold;\">Внимание! Ваш уровень доступа ($ul) ниже необходимого ($pl) для сохранения изменений! Изменения не будут сохранены!</span>";    
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
            echo "<b><font color=\"red\">Уровень доступа пользователя $nick ($ul) недостаточен для этой ($pl) операции!</font></h3>Залогиниться, зарегистрироваться или сменить аккаунт можно <a href=\"http://forums.airbase.ru\">форумах</a>.";
            die();
        }
    }

    class User
    {
    	var $id;

		function User($_id = NULL)
		{
			if(empty($_id))
				$id = @$_COOKIE['login'];
			else
				$id = $_id;
		}
    	
    	function data($data, $default=NULL)
    	{
    		return user_data($data, $this->id, $default);
    	}
	
		function generate_compiled_passhash($salt, $md5_once_password)
		{
			return md5( md5( $salt ) . $md5_once_password );
		}

	    function do_login($user, $password)
    	{
        	$member_id = user_data('member_id', $user);
			
			if(!$member_id)
			{
				echo("<b>Неизвестный пользователь '$user'</b>'");
				return false;
			}
			
			$lp = user_data('converge_pass_hash', $user);
			$pass_hash = $this->generate_compiled_passhash(user_data('salt', $member_id), md5($password));

//			exit( "pw=$password, ph='$pass_hash', lp=$lp;");
			
			if($pass_hash != $lp)
			{
				echo "<b>Неправильный пароль пользователя '$user'</b>'";
				return false;
			}

			SetCookie("member_id", $member_id, time()+2592000,"/", $_SERVER['HTTP_HOST']);
			SetCookie("pass_hash", $pass_hash, time()+2592000,"/", $_SERVER['HTTP_HOST']);

			$_COOKIE['member_id'] = $member_id;
			$_COOKIE['pass_hash'] = $pass_hash;
			
//			exit("<b>Login successful!<br><br></b><br />");
		}

		function do_logout()
		{
			SetCookie("member_id","",0,"/");
			SetCookie("pass_hash","",0,"/");
			$_COOKIE['member_id'] = "";
			$_COOKIE['pass_hash'] = "";
		}

		function get_page()
		{
			return $GLOBALS['cms']['main_host_uri'] . "/users/~".$this->id."/";
		}

	}
?>
