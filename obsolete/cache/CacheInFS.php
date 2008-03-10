<?
	$GLOBALS['bors']['config']['cache_in_fs_path'] = '/xxwww/sites/aviaport/tmp/fs-cache/';

	// $_SERVER['DOCUMENT_ROOT']."/cache/fs/";

	include_once("funcs/filesystem_ext.php");

    class Cache
    {
        var $last;
		var $last_type;
		var $last_key;
		var $last_uri;
        
        function Cache()
        {
        }

        function get($type, $key, $uri='', $default=NULL)
        {
			$this->last_type = $type;
			$this->last_key  = $key;
			$this->last_uri  = $uri;

			if($GLOBALS['cms']['cache_disabled'])
           		return ($this->last = $default);

			$hmd = md5("$type:$key");
			$file = $this->get_file_name($hmd);
			
//			echo "mod = ".strftime("%Y-%m-%d %H:%M:%S\n", filemtime($file));

			if(@filemtime($file) > time())
			{
//				echo "*";
				return $this->last = file_get_contents($file);
			}
			
//			if(@filemtime($file) == 0)
//				echo "nc:$type,$key,$uri";
			
			@unlink($file);

			if($uri)
			{
				$dir = $this->get_dir_name(md5($uri)).".d";
				@unlink("$dir/$hmd.cfs");
				@unlink($dir);
			}
			
			if($key)
			{
				$dir = $this->get_dir_name(md5($key)).".d";
				@unlink("$dir/$hmd.cfs");
				@unlink($dir);
			}

            return $this->last = $default;
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
		
            $hmd = md5("$type:$key");
			
			$file = $this->get_file_name($hmd);
			
			file_put_contents($file, $value);
			touch($file, time() + $time_to_expire);
			if($this->last_uri)
			{
				$dir = $this->get_dir_name(md5($this->last_uri));
				@symlink($file, "$dir/$hmd.cfs");
				@touch("$dir/$hmd.cfs", time() + $time_to_expire);
			}

			if($key)
			{
				$dir = $this->get_dir_name(md5($key));
				@symlink($file, "$dir/$hmd.cfs");
				@touch("$dir/$hmd.cfs", time() + $time_to_expire);
			}

            return $this->last = $value;
        }

        function last()
        {
            return $this->last;
        }

        function clear_by_id($key)
        {
			if($key)
			{
				$dir = $this->get_dir_name(md5($key));
				foreach(scandir($dir) as $f)
					@unlink(@readlink("$dir/$f"));
			}
        }

        function clear_by_uri($uri)
        {
			if($uri)
			{
				$dir = $this->get_dir_name(md5($uri));
				foreach(scandir($dir) as $f)
					@unlink(@readlink("$dir/$f"));
			}
        }

		function get_file_name($id)
		{
			$sub1 = substr($id,0,2);
			$sub2 = substr($id,2,2);
			$path = "{$GLOBALS['bors_data']['config']['cache_in_fs_path']}$sub1/$sub2";
			mkpath($path, 0777);
			return "$path/$id.cfs";
		}

		function get_dir_name($id)
		{
			$dir = $this->get_file_name($id).".d";
			@mkdir($dir, 0777);
			return $dir;
		}
    }
