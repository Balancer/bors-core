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
            $this->dbh = &new DataBase($GLOBALS['cms']['mysql_cache_database']);
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

			$tab = substr($hmd, 0, 2);
			
            $row = $this->dbh->get("SELECT `value`, `expire_time`, 0 as `count` FROM `cache_$tab` WHERE `hmd`=0x$hmd");
			$this->last = $row['value'];

			if($row['expire_time'] <= time())
			{
				$this->last = NULL;
	            $this->dbh->query("DELETE FROM `cache_$tab` WHERE `hmd`=0x$hmd");
			}
			

//            if($this->last)
//                $this->dbh->query("UPDATE `cache` SET `access_time` = ".time().", `count`=".(intval($row['count'])+1)." WHERE `hmd`=0x$hmd");

            return ($this->last ? $this->last : $default);
        }

        function set($type, $key = NULL, $value = NULL, $time_to_expire = 86401)
        {
			if($value == NULL && $time_to_expire == 86401)
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

			$tab = substr($hmd, 0, 2);

            $this->dbh->query("REPLACE `cache_$tab` (`type`,`key`,`hmd`,`uri`,`value`,`access_time`,`create_time`,`expire_time`) VALUES ('".addslashes($type)."','".addslashes($key)."',0x$hmd,'".addslashes($this->last_uri)."','".addslashes($value)."',".time().",".time().",".(time()+$time_to_expire).") ", true);
//			$GLOBALS['log_level'] = 2;

            return $this->last = $value;
        }

        function last()
        {
            return $this->last;
        }

        function clear_check($type, $time)
        {
			for($i=0; $i<256;$i++)
	            $this->dbh->query("DELETE FROM `cache_".sprintf("%02x", $i)."` WHERE `type`='$type' AND `create_time` < ".(time()-$time));
        }

        function clear_by_id($key)
        {
			$key = addslashes($key);
			for($i=0; $i<256;$i++)
				$this->dbh->query("DELETE FROM `cache_".sprintf("%02x", $i)."` WHERE `key` = '$key'");
        }

        function clear_by_uri($uri)
        {
			for($i=0; $i<256;$i++)
				$this->dbh->query("DELETE FROM `cache_".sprintf("%02x", $i)."` WHERE `uri` = '".addslashes($uri)."'");
        }

        function get_array_by_uri($uri)
        {
			$ret = array();
			for($i=0; $i<256;$i++)
				$ret = array_merge($ret, $this->dbh->get_array("SELECT DISTINCT value FROM `cache_".sprintf("%02x", $i)."` WHERE `uri` = '".addslashes($uri)."'"));

			return $ret;
        }

        function clear_by_type($type)
        {
			for($i=0; $i<256;$i++)
				$this->dbh->query("DELETE FROM `cache_".sprintf("%02x", $i)."` WHERE `type` LIKE '".addslashes($type)."'");
        }

        function clear($type, $key)
        {
			for($i=0; $i<256;$i++)
				$this->dbh->query("DELETE FROM `cache_".sprintf("%02x", $i)."` WHERE `type` = '".addslashes($type)."' AND `key` = '".addslashes($key)."'");
        }
    }
