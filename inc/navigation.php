<?
//    require_once('debug.php');

    function goq($uri, $permanent = false, $time = 0, $exit = false)
	{
		if(strpos($uri, '?') === false && !empty($_SERVER['QUERY_STRING']))
			$uri .= '?'.$_SERVER['QUERY_STRING'];
			
		return go($uri, $permanent, $time, $exit);
	}

    function go($uri, $permanent = false, $time = 0, $exit = false)
    {
		bors()->changed_save();

		if(config('do_not_exit'))
			debug_exit("Go to <a href=\"{$uri}\">{$uri}</a>");

		if(config('bors_version_show'))
			@header("X-bors-go: {$uri}");
	
        if(!headers_sent($filename, $linenum) && $time==0) 
        {
			if($permanent)
	            header("Status: 301 Moved Permanently");
			else
	            header("Status: 302 Moved Temporarily");
			if(preg_match("!\n!", $uri))
				echolog("cr in uri '$uri'", 1);
				
			header("Location: $uri");

			if($exit)
				bors_exit();
        }

//		if($text)
//	        echo "Load page <a href=\"$uri\">$uri</a><br />\n";
			
		echo "<meta http-equiv=\"refresh\" content=\"$time; url=$uri\">";

//        debug("headers already out in $filename:$linenum");

		if($exit)
			bors_exit();

		return true;
    }

    function go_ref($def = "/")
	{
		unset($_SERVER['QUERY_STRING']);
		
		if(!empty($GLOBALS['ref']))
			go($GLOBALS['ref']);

		if(!empty($_SERVER['HTTP_REFERER']))
			go($_SERVER['HTTP_REFERER']);
			
		go($def);
	}
