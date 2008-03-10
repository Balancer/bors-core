<?
    require_once("funcs/DataBase.php");

    class Cache
    {
        var $dbh;
        var $last;
		var $last_type;
		var $last_key;
		var $last_uri;
        
        function Cache()
        {
            $this->dbh = &new driver_mysql(config('cache_database'));
        }

        function get($type, $key, $uri='', $default=NULL)
        {
			$this->last_type = $type;
			$this->last_key  = $key;
			$this->last_uri  = $uri;

//            echo "Get from cache $type:$key:$uri<br>";

			if($GLOBALS['cms']['cache_disabled'])
           		return ($this->last = $default);

            $hmd = md5("$type:$key");
			
            $row = $this->dbh->get("SELECT `value`, `expire_time`, 0 as `count` FROM `cache` WHERE `hmd`='$hmd'");
			$this->last = $row['value'];

			if($row['expire_time'] <= time())
			{
				$this->last = NULL;
	            $this->dbh->query("DELETE FROM `cache` WHERE `hmd`='$hmd'");
			}
			

//            if($this->last)
//                $this->dbh->query("UPDATE `cache` SET `access_time` = ".time().", `count`=".(intval($row['count'])+1)." WHERE `hmd`='$hmd'");

            return ($this->last ? $this->last : $default);
        }

        function set($type, $key = NULL, $value = NULL, $time_to_expire = 604800)
        {
			if($value == NULL && $time_to_expire == 604800)
			{
				$value = $type;
				if($key != NULL)
					$time_to_expire = $key;
				$type = $this->last_type;
				$key  = $this->last_key;
			}
		
//        	return $this->last = $value;
//            $GLOBALS['log_level']=4;
            $hmd = md5("$type:$key");
//            echo "Set cache $type:$key:{$this->last_uri}<br/>\n";
//			$GLOBALS['log_level'] = 10;
            $this->dbh->query("REPLACE `cache` (`type`,`key`,`hmd`,`uri`,`value`,`access_time`,`create_time`,`expire_time`) VALUES ('".addslashes($type)."','".addslashes($key)."','$hmd','".addslashes($this->last_uri)."','".addslashes($value)."',".time().",".time().",".(time()+$time_to_expire).") ", true);
//			$GLOBALS['log_level'] = 2;

            return $this->last = $value;
        }

        function last()
        {
            return $this->last;
        }

        function clear_check($type, $time)
        {
            $this->dbh->query("DELETE FROM `cache` WHERE `type`='$type' AND `create_time` < ".(time()-$time));
        }

        function clear_by_id($key)
        {
			$this->dbh->query("DELETE FROM `cache` WHERE `key` = '".addslashes($key)."'");
        }

        function clear_by_uri($uri)
        {
			$this->dbh->query("DELETE FROM `cache` WHERE `uri` = '".addslashes($uri)."'");
        }

        function get_array_by_uri($uri)
        {
			return $this->dbh->get_array("SELECT DISTINCT value FROM `cache` WHERE `uri` = '".addslashes($uri)."'");
        }

        function clear_by_type($type)
        {
			$this->dbh->query("DELETE FROM `cache` WHERE `type` LIKE '".addslashes($type)."'");
        }

        function clear($type, $key)
        {
			$this->dbh->query("DELETE FROM `cache` WHERE `type` = '".addslashes($type)."' AND `key` = '".addslashes($key)."'");
        }
    }
