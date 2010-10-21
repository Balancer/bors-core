<?php
	$punbb_db = 'punbb';

	// db table get-field where-field
    $GLOBALS['forums_data']=array(
            'nick' => "$punbb_db users username id",
            'name' => "$punbb_db users username id",
            'password' => "$punbb_db users password id",
            'joined'	=> "$punbb_db users registered id",
            'email'		=> "$punbb_db users email id",
            'salt'	=> "$punbb_db users salt id",
			'group'	=>	"$punbb_db users group_id id",
        );

    function user_data($key, $user=NULL, $def='')
    {
        if(is_global_key("user_data($user)", $key))
            return global_key("user_data($user)", $key);

		$us = new User($user);
	
		$member_id = $us->data('id');

        if(is_global_key("user_data($member_id)", $key))
            return global_key("user_data($member_id)", $key);

        if($key == 'member_id' || $key == 'id')
            return $member_id;

//        echo "Try get data for user '$user' ($member_id) (cookie = ".@$_COOKIE['member_id'].")for key '$key' (def=$def)<br>\n";
			
		if(!$member_id)
			if($key == 'nick' || $key == 'name')
				return $def ? $def : get_ip_nick();
			else
				return $def;//'Unknown(parameter error)';

        $db = new DataBase('USERS');
		
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
        
        if(!isset($forums_data[$key]) || !$forums_data[$key])
            $value = $db->get("SELECT `value` FROM `users_data` WHERE `member_id`=$member_id AND `key`='".addslashes($key)."'");
        else
		{
			list($base, $table, $field, $key) = explode(" +",$forums_data[$key]);
	        $dbp = new DataBase('punbb');
            $value = $dbp->get("SELECT $field FROM $base.$table WHERE $key=$member_id");
       	}
       
//        echo "val=$value<br>\n";

    if(0 && $key=='level' && $member_id==1)
    {
    	return 1;
    }

        return $value ? set_global_key("user_data($member_id)",$key, $value) : $def;
    }

    function set_user_data($key, $value, $user=NULL)
    {
        global $forums_data;
		$us = new User($user);
        $member_id = $us->data('id');

//      	echo("set '$key'='$value' for $user<br/>");
		
        if(!$member_id)
            return false;

        if(empty($forums_data[$key]))
		{
	        $db = new DataBase('USERS');
            $db->store('users_data',"`member_id`=$member_id AND `key`='".addslashes($key)."'",array('member_id'=>$member_id,'key'=>$key,'value'=>$value));
        }
		else
		{
	        $db = new DataBase('punbb');
			list($base, $table, $field, $where) = explode(" +",$forums_data[$key]);
            $db->query("UPDATE `$base`.`$table` SET `$field` = '".addslashes($value)."' WHERE `$where` = ".intval($member_id));
		}

        return set_global_key("user_data($member_id)", $key, $value);
    }

    function user_data_array($key, $user=NULL, $def=array())
    {
        if(!$member_id = get_user($user))
			return isset($def) ? $def : false;

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

    function access_allowed($page, $hts=NULL)
    {
        if(empty($hts))
            $hts = new DataBaseHTS('HTS');

        $base_page_access = 3;//$hts->base_value('default_access_level', 3);
        $ul = intval(user_data('level', NULL, 1));

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

		function User($id = NULL)
		{
			if($id)
			{
				$this->id = $id;
    		    if(!preg_match("!^\d+$!", $id))
				{
					$db = new DataBase('punbb');
            		$this->id = $db->get("SELECT id FROM users WHERE username = '".addslashes($id)."'");
				}
			}
			else
			{
				$this->check_salt();
			}
		}

        function get($key, $default=NULL)
        {
            if($ret = $this->data($key, NULL))
                return $ret;
											
            $hts = &new DataBaseHTS('HTS');
            if($ret = $hts->get_data("forum_user://{$this->id}/", $key))
            	return $ret;
																			
        	return $default;
		}
    	
    	function data($data, $default=NULL)
    	{
	        if($data == 'member_id' || $data == 'id')
	            return $this->id;

    		return user_data($data, $this->id ? $this->id : NULL, $default);
    	}

		function set_data($data, $value)
		{
			return set_user_data($data, $value, $this->id);
		}
	
		function cookie_hash()
		{
			return sha1(bors_lower($this->data('salt')) . $this->data('password'));
		}

		function cookie_hash_update($expired = -1)
		{
			if($expired == -1)
				$expired = time()+86400*365;

			$cookie_hash = sha1(rand());
			$this->set_data('salt', $cookie_hash);

			SetCookie("user_id", $this->get('id'), $expired, "/", '.'.$_SERVER['HTTP_HOST']);
			SetCookie("cookie_hash", $cookie_hash, $expired, "/", '.'.$_SERVER['HTTP_HOST']);
			
			$_COOKIE['user_id'] = $this->get('id');
			$_COOKIE['cookie_hash'] = $cookie_hash;
			return $this->cookie_hash();
		}

	    function check_salt()
		{
			$user_hash_password = @$_COOKIE['cookie_hash'];
			
			if(is_global_key('user-id-cookie-hash', $user_hash_password))
			{
				$this->id = global_key('user-id-cookie-hash', $user_hash_password);
				return;
			}

			$db = &new driver_mysql('punbb');

			$this->id = 1;
			if($user_hash_password)
			{
				$this->id = intval($db->get("SELECT id FROM punbb.users WHERE user_cookie_hash = '".addslashes($user_hash_password)."' LIMIT 1"));
/*				if(!$this->id)
				{
					$db = &new DataBase('USERS');
					$this->id = intval($db->get("SELECT user_id FROM salt WHERE host = '".addslashes($_SERVER['HTTP_HOST'])."' AND cookie_hash = '".addslashes($user_hash_password)."' LIMIT 1"));
					if(!$this->id)
						$this->id = 1;
				}
*/				
				set_global_key('user-id-cookie-hash', $user_hash_password, $this->id);
			}
//			echo("=={$this->id}");
		}

	    function check_password($password, $handle_errors = true)
    	{
			$sha_password = sha1(bors_lower($this->data('name')) . $password);
			$user_sha_password = $this->data('password');
	
			if(!$handle_errors)
				return ($password != '') && ($user_sha_password == $sha_password);

        	if(!$password)
	        {
    	        $nick = user_data('nick');
        	    echo "<h3><span style=\"text-color: red;\">Пароль пользователя $nick ($member_id) не может быть пустой!</span></h3>Залогиниться, зарегистрироваться или сменить аккаунт можно <a href=\"http://forums.airbase.ru/\">форуме Авиабазы</a>.<br><span style=\"font-size: xx-small;\">Внимание! Вместо старой системы регистрации теперь будет использоваться новая, объединённая с регистрацией на форумах!";
	            die();
	        }

    	    if($sha_password != $user_sha_password)
        	{
            	$nick=user_data('nick');
	            echo "<h3><span style=\"text-color: red;\">Ошибка пароля или логина пользователя $nick! ($member_id)</span></h3>Залогиниться, зарегистрироваться или сменить аккаунт можно <a href=\"http://forums.airbase.ru/\">форуме Авиабазы</a>.<br><span style=\"font-size: xx-small;\">Внимание! Вместо старой системы регистрации теперь будет использоваться новая, объединённая с регистрацией на форумах!";
    	        die();
        	}
	    }

	    function do_login($user, $password, $handle_error = true)
    	{
	        $db = new DataBase('punbb');
//			loglevel(10);
			$this->id = intval($db->get("SELECT id FROM punbb.users WHERE username = '".addslashes($user)."' LIMIT 1"));
//			echo "Id={$this->id}";
//			loglevel(2);

//			exit("=1= {$this->id} ==<br/>");

			if(!$this->id)
				return ec("Неизвестный пользователь '").$user."'";
			
//			exit("=2= $user/$password: {$this->id}==<br/>");

			$test = $this->check_password($password, $handle_error);
			if(!$test)
				return ec("Ошибка пароля пользователя '").$user."'";

			$cookie_hash = $db->get("SELECT user_cookie_hash FROM punbb.users WHERE id = ". intval($this->id));
			if(!$cookie_hash)
			{
				$cookie_hash = $this->cookie_hash_update();
				$db->query("UPDATE punbb.users SET user_cookie_hash = '".addslashes($cookie_hash)."' WHERE id = ". intval($this->id));
			}
//			exit( "pw=$password, ch='$cookie_hash';<br/>");

/*			$db = &new DataBase('USERS');
			$db->replace('salt', array(
				'user_id' => $this->id,
				'host' => $_SERVER['HTTP_HOST'],
				'salt' => $this->data('salt'),
				'cookie_hash' => $cookie_hash,
			));
*/
			SetCookie("user_id", $this->id, time() + 86400*365, "/", $_SERVER['HTTP_HOST']);
			SetCookie("cookie_hash", $cookie_hash, time() + 86400*365, "/", $_SERVER['HTTP_HOST']);
			
//			SetCookie("cookie_hash", $cookie_hash, time()+86400*365,"/", $_SERVER['HTTP_HOST']);
//			$_COOKIE['cookie_hash'] = $cookie_hash;
			
			return 0;
		}

		function do_logout()
		{
//			exit("Do logout");
//			SetCookie("cookie_hash", "none", 0, "/");
//			$_COOKIE['cookie_hash'] = "none";
//			SetCookie("user_id", "0", 0, "/");
//			$_COOKIE['user_id'] = "0";
			$this->cookie_hash_update(0);
		}

		function get_page()
		{
			return config('main_host_url') . "/users/~".$this->id."/";
		}
	}
