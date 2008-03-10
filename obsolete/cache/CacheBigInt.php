<?
    require_once("funcs/DataBase.php");

    class Cache
    {
        var $dbh;
        var $last;
		var $last_type;
		var $last_key;
		var $last_uri;
		var $last_hmd;
        
        function Cache()
        {
            $this->dbh = &new DataBase($GLOBALS['cms']['mysql_cache_database']);
        }

        function get($type, $key, $uri='', $default=NULL)
        {
			$this->last_type = $type = "0x".substr(md5($type), -16);
			$this->last_key  = $key  = "0x".substr(md5($key), -16);
			$this->last_uri  = $uri  = "0x".substr(md5($uri), -16);
            $this->last_hmd  = $hmd  = "0x".substr(md5("$type:$key"), -16);
		
			if($GLOBALS['cms']['cache_disabled'])
           		return ($this->last = $default);

			$tab = substr($hmd, 2, 1);
			
            $row = $this->dbh->get("SELECT `value`, `expire_time`, `count` FROM `cache_$tab` WHERE `hmd`=$hmd");
			$this->last = $row['value'];

			if($row['expire_time'] <= time())
			{
				$this->last = NULL;
	            $this->dbh->query("DELETE FROM `cache_$tab` WHERE `hmd`=$hmd");
			}

			if($this->last)
				$this->dbh->query("UPDATE `cache_$tab` SET `access_time` = ".time().", `count`=".(intval($row['count'])+1)." WHERE `hmd`=$hmd");

            return ($this->last ? $this->last : $default);
        }

        function set($value, $time_to_expire = 86400)
        {
			$tab = substr($this->last_hmd, 2, 1);

            $this->dbh->replace("cache_$tab", array(
				'int hmd'	=> $this->last_hmd,
				'int type'	=> $this->last_type,
				'int key'	=> $this->last_key,
				'int uri'	=> $this->last_uri,
				'value'	=> $value,
				'int access_time' => NULL,
				'int create_time' => time(),
				'int expire_time' => time() + $time_to_expire,
			));

            return $this->last = $value;
        }

        function last()
        {
            return $this->last;
        }

        function clear_by_id($key)
        {
			$key = "0x".substr(md5($key), -16);
			for($i=0; $i<16;$i++)
				$this->dbh->query("DELETE FROM `cache_".sprintf("%x", $i)."` WHERE `key` = $key");
        }

        function clear_by_uri($uri)
        {
			$uri = "0x".substr(md5($uri), -16);
			for($i=0; $i<16;$i++)
				$this->dbh->query("DELETE FROM `cache_".sprintf("%x", $i)."` WHERE `uri` = $uri");
        }

        function clear_by_type($type)
        {
			$type = "0x".substr(md5($type), -16);
			for($i=0; $i<16;$i++)
				$this->dbh->query("DELETE FROM `cache_".sprintf("%x", $i)."` WHERE `type` = $type");
        }

        function get_array_by_uri($uri)
        {
			$uri = "0x".substr(md5($uri), -16);
			$ret = array();
			for($i=0; $i<16;$i++)
				$ret = array_merge($ret, $this->dbh->get_array("SELECT DISTINCT value FROM `cache_".sprintf("%x", $i)."` WHERE `uri` = $uri"));

			return $ret;
        }
    }
