<?
	function strip_text($text, $len=192)
	{
    	$text=to_one_string($text);

	    while(preg_match("#^(.*)<!\-\-QuoteBegin.*?\-\->.+?<!\-\-QuoteEEnd\-\->#is",$text))
    	    $text=preg_replace("#^(.*)<!\-\-QuoteBegin.*?\-\->.+?<!\-\-QuoteEEnd\-\->#is","$1",$text);

    // 
	    $text=preg_replace("!(^|<p>|<br>|<br />)+(<font[^>]+>)(<p>)?[^\s<]*>.+?(<br />|<br>|<p>|$)+!i","",$text);
    	$text=preg_replace("!(^|<p>|<br>|<br />)+(<font[^>]+>)(<a [^>]+>)?[^\s<]*</a>>.+?(<br />|<br>|<p>|$)+!i","",$text);
	    $text=preg_replace("!<font size=\-2 color=#\d\d\d\d\d\d><b>.+?</b>>.+?</font>(<br>)+!is","",$text);

    	$text=preg_replace("!(<br>|<br />)+!i","<br />",$text);

//    $text=preg_replace("!</?(table|tr|td|h\d|br|p)[^>]*>!i"," ",$text);
	    $text=str_replace("\n"," ",$text);
    	$text=preg_replace("/\s+/"," ",$text);
	    $text=preg_replace("/^\s+/","",$text);
    	$text=preg_replace("/\s+$/","",$text);
	    $text=preg_replace("/^#nav(.+?)#/","",$text);
    	$text=preg_replace("!\-+!","-",$text);

	    if(strlen($text)>$len)
    	{
        	$res="";
	        $do_flag=1;
    	    $in_tag=0;
        	while($do_flag && $text)
	        {
    	        $c=substr($text,0,1);
        	    $text=substr($text,1);
            	$res.=$c;
	            if($c=='<') $in_tag++;
    	        if($c=='>' && $in_tag>0) $in_tag--;
        	    if(!$in_tag && strlen($res)>=$len)
            	    $do_flag=0;
	        }
    	    $text=$res."&#133";
	    }

	    $close_tags=split(" ","a i b u s font div span td tr tt table blockquote pre xmp");

    	for($i=0, $count = count($close_tags); $i<$count; $i++)
	    {
			$tag = $close_tags[$i];
        	$n = preg_match_all("!<$tag(\s|>)!i", $text, $m) - preg_match_all("!</$tag(\s|>)!i", $text, $m);
	        if($n > 0)
        	    while($n--)
            	    $text .="</$tag>";
    	}

	    return "$text";
	}

	function to_one_string($s)
	{
	    if(sizeof($s)>1) $s=join("\n",$s);
    	$s=str_replace("\n","<br />",$s);
	    $s=str_replace("\|","&#124;",$s);
    	$s=str_replace("\r","",$s);

	    return $s;
	}

	function quote_fix($text)
	{
		if(!empty($GLOBALS['bors_data']['config']['gpc']) && preg_match("!\\\\!", $text))
			return stripslashes($text);

		return $text;
	}
