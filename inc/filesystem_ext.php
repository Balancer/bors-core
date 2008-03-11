<?
    function abs_path_from_relative($uri, $page)
    {
        if(preg_match("!^\w+://!", $uri))
            return $uri;
        
        if(preg_match("!^/!", $uri))
            return 'http://'.$_SERVER['HTTP_HOST'].$uri;

        return "$page$uri";
    }

    function mkpath($strPath, $mode=0777)
    {
        if(is_dir($strPath)) 
            return true;
  
        $pStrPath = dirname($strPath);

        if(!mkpath($pStrPath, $mode)) 
            return false;

  		$err = @mkdir($strPath, $mode);
		@chmod($strPath, $mode);
		return $err;
    }

	function smart_size($size)
	{
		if($size<1024)
			return $size.ec("Б");

		$size = $size/1024;

		if($size<1024)
			return round($size,2).ec("КБ");

		return round($size/1024,2).ec("МБ");
	}

	if(!function_exists("scandir"))
		require_once("include/php4/scandir.php");

	function rec_rmdir($dir, $delete_self = true, $mask = '.*')
	{
    	if(!$dh = @opendir($dir))
			return;

	    while(($obj = readdir($dh))) 
		{
	        if($obj=='.' || $obj=='..')
				continue;

			if(!preg_match("!^{$mask}$!", $obj))
				continue;

	        if(!@unlink($dir.'/'.$obj))
				rec_rmdir($dir.'/'.$obj, true, $mask);
	    }

		@closedir($dh);
		
	    if ($delete_self)
	        @rmdir($dir);
	}
