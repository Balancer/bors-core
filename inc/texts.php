<?php

require_once('strings.php');

	function strip_text($text, $len=192, $more_text = '&#133;')
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

	    if(bors_strlen($text)>$len)
    	{
        	$res="";
	        $do_flag=1;
    	    $in_tag=0;
        	while($do_flag && $text)
	        {
    	        $c = bors_substr($text,0,1);
        	    $text = bors_substr($text,1);
            	$res.=$c;
	            if($c=='<') $in_tag++;
    	        if($c=='>' && $in_tag>0) $in_tag--;
        	    if(!$in_tag &&  bors_strlen($res)>=$len)
            	    $do_flag=0;
	        }
    	    $text = $res . $more_text;
	    }


	    return bors_close_tags("$text");
	}

function bors_close_tags($text)
{
    $close_tags = explode(" ","a i b u s font div option select small span td tr tt table blockquote pre xmp");

   	for($i=0, $count = count($close_tags); $i<$count; $i++)
    {
		$tag = $close_tags[$i];
       	$n = preg_match_all("!<$tag(\s|>)!i", $text, $m) - preg_match_all("!</$tag(\s|>)!i", $text, $m);
        if($n > 0)
       	    while($n--)
           	    $text .="</$tag>";
   	}

   	return $text;
}

function to_one_string($s)
{
    if(sizeof($s)>1) 
    	$s=join("\n",$s);

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

require_once('classes/inc/text/Stem_ru-'.config('internal_charset').'.php');

function bors_text_clear($text, $morfology = true, $spacer = ' ')
{
	$text = preg_replace('/&\w+;/', ' ', $text);
	$text = preg_replace('/&#\d+;/', ' ', $text);
	$text = str_replace(
		array('«','»','№'), 
		array(' ',' ',' '),
		$text);
	$text = preg_replace("![\x01-/ :-@ [-` {-~]!x", ' ', $text);
	$result = trim(bors_lower(preg_replace('/\s{2,}/', ' ', $text)));
	if($morfology)
	{
		static $Stemmer = false;
		static $cache = array();

		if(!$Stemmer)
			$Stemmer = new Lingua_Stem_Ru();

		$words = array();
		foreach(explode(' ', $result) as $word)
			if(array_key_exists($word, $cache))
				$words[] = $cache[$word];
			else
				$words[] = $cache[$word] = $Stemmer->stem_word($word);

		$result = join(' ', $words);
	}

	$result = ' '.$result.' ';
	if($spacer != ' ')
		$result = str_replace(' ', $spacer, $result);

	return $result;
}
