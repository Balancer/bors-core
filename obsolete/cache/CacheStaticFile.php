<?
    require_once("funcs/DataBase.php");
    require_once("funcs/DataBaseHTS.php");

    class CacheStaticFile
    {
		var $_file;
		var $uri;
		var $page;
		var $original_uri;
	
        function CacheStaticFile($uri=NULL, $page=1)
        {
			$this->set_name($uri, $page);
        }

		function set_name($uri, $page=1)
		{
			if(!$uri)
				debug_exit('Empty uri for static cache');
		
//			echo "Set name '$uri'<br/>";
			$this->uri  = $uri;
			$this->page  = $page;
			$this->original_uri  = $uri;
			if(!empty($GLOBALS['bors']))
			{
				$cfg = $GLOBALS['bors']->config();
				if($cfg->cache_uri())
					$this->original_uri = $cfg->cache_uri();
			}
			
			$this->_file = $_SERVER['DOCUMENT_ROOT'].preg_replace('!http://[^/]+!', '', $uri);
			
			if(preg_match("!/[^\.]+$!", $uri))
				$uri .= "/";
			
			if($uri{strlen($uri)-1}=='/')
			{

				if($this->page > 1)
					$title = "index-$page.html";
				else
					$title = "index.html";

				$this->_file .= $title;
				$this->uri  .= $title;
			}
//			echo "'$uri'";
//			echo "File = {$this->_file}<br/>";
		}

		function save(&$content, $mtime = 0, $expire_time = 0)
		{
			if(config('static_cache_disabled'))
				return $content;
		
            $db = &new DataBase($GLOBALS['cms']['mysql_cache_database']);
			
//			print_d($this->original_uri);
//			@unlink($db->get("SELECT file FROM cached_files WHERE original_uri = '".addslashes($this->original_uri)."'"));
			@unlink($db->get("SELECT file FROM cached_files WHERE uri = '".addslashes($this->uri)."'"));

//			echo "save file '{$this->_file}, exp=$expire_time'<br />";
			if($expire_time == 0)
				return $content;

			require_once("funcs/filesystem_ext.php");
			mkpath(dirname($this->_file));
			
			if(!$fh = fopen($this->_file, 'a+'))
				die("Can't open write {$this->_file}");
			if(!flock($fh, LOCK_EX))
				die("Can't lock write {$this->_file}");
			if(!ftruncate($fh, 0))
				die("Can't truncate write {$this->_file}");

			fwrite($fh, $content);
			fclose($fh);
			
			@chmod($this->_file, 0664);

//			echo "mtime = ".strftime("%d.%m.%Y %H:%M<br />", $mtime);
			if($mtime)
				touch($this->_file, $mtime);
			
			$db->replace('cached_files', // "original_uri = '".addslashes($this->original_uri)."'", 
				array(
					'file'			=> $this->_file,
					'uri'			=> $this->uri,
					'original_uri'	=> $this->original_uri,
					'last_compile'	=> time(),
					'int expire_time'	=> $expire_time > 0 ? time() + $expire_time : -1,
				)
			);
			
			return $content;
		}
		
		function get_name($uri)
		{
            $db = &new DataBase($GLOBALS['cms']['mysql_cache_database']);
			
			return $db->get("SELECT uri FROM cached_files WHERE original_uri = '".addslashes($uri)."' ORDER BY last_compile DESC LIMIT 1");
		}

		function get_file($uri)
		{
            $db = &new DataBase($GLOBALS['cms']['mysql_cache_database']);
			
			return $db->get("SELECT file FROM cached_files WHERE original_uri = '".addslashes($uri)."' ORDER BY last_compile DESC LIMIT 1");
		}
		
		function clean($original_uri)
		{
            $db = &new DataBase($GLOBALS['cms']['mysql_cache_database']);
			
			$files = $db->get_array("SELECT file FROM cached_files WHERE original_uri = '".addslashes($original_uri)."'");
			$db->query("DELETE FROM cached_files WHERE original_uri = '".addslashes($original_uri)."'");
			foreach($files as $file)
			{
				@unlink($file);
				@rmdir(dirname($file));
			}
			
		}
    }
