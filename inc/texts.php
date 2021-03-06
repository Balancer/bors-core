<?php

use B2\Cfg;

require_once('strings.php');

	function strip_text($text, $len=192, $more_text = NULL, $microstyle = false, $wrap = 0)
	{
		if(is_null($more_text))
			$more_text = ec('…');

//		$text = to_one_string($text);

		while(preg_match("#^(.*)<!\-\-QuoteBegin.*?\-\->.+?<!\-\-QuoteEEnd\-\->#is",$text))
			$text=preg_replace("#^(.*)<!\-\-QuoteBegin.*?\-\->.+?<!\-\-QuoteEEnd\-\->#is","$1",$text);

		$text=preg_replace("!(^|<p>|<br>|<br />)+(<font[^>]+>)(<p>)?[^\s<]*>.+?(<br />|<br>|<p>|$)+!i","",$text);
		$text=preg_replace("!(^|<p>|<br>|<br />)+(<font[^>]+>)(<a [^>]+>)?[^\s<]*</a>>.+?(<br />|<br>|<p>|$)+!i","",$text);
		$text=preg_replace("!<font size=\-2 color=#\d\d\d\d\d\d><b>.+?</b>>.+?</font>(<br>)+!is","",$text);

		$text=preg_replace("!(<br>|<br />)+!i","<br />",$text);

//	$text=preg_replace("!</?(table|tr|td|h\d|br|p)[^>]*>!i"," ",$text);
		$text=str_replace("\n"," ",$text);
		$text=preg_replace("/\s+/"," ",$text);
		$text=preg_replace("/^\s+/","",$text);
		$text=preg_replace("/\s+$/","",$text);
		$text=preg_replace("/^#nav(.+?)#/","",$text);
		$text=preg_replace("!\-+!","-",$text);

		if($microstyle)
		{
			$text = preg_replace('!<br[^>]*>!', "\n", $text);
			$text = preg_replace('!^\s*\S+>.*$!m', '', $text);
			$text = str_replace("\n", " ", $text);
			$text = preg_replace("/\s{2,}/", " ", trim($text));
			$text = preg_replace("!\[/?\w+[^\]]*\]!", " ", trim($text));
			$text = strip_tags($text);
			$text = str_replace(array(':eek:'), array('o_O'), $text);
			if(bors_strlen($text) > $len)
			{
				$text = bors_substr($text, 0, $len-bors_strlen($more_text));
				$space_pos = bors_strrpos($text, ' ');
				if($space_pos !== false)
					$text = bors_substr($text, 0, $space_pos);

				$text = bors_close_tags($text);

				$text .= $more_text;
			}

//			if($wrap)
//				$text = str_replace('———', '&shy;', htmlspecialchars(blib_string::wordwrap($text, $wrap, '———',true)));

			return $text;
		}

		if(bors_strlen($text) > $len)
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

			$res = bors_close_tags($res);

			$text = $res . $more_text;
		}

		if(Cfg::get('is_test'))
			echo $text, PHP_EOL, PHP_EOL;

		return $text;
	}

function bors_close_tags($html) { return blib_html::close_tags($html); }

function bors_close_bbtags($text)
{
	$close_tags = explode(" ", "a b blockquote dd div dl dt em embed font i iframe object option p param pre s select small span table td tr tt u xmp");

   	foreach($close_tags as $tag)
	{
	   	$n = preg_match_all("!\[$tag(\s|\])!i", $text, $m) - preg_match_all("!\[/$tag(\s|\])!i", $text, $m);

		if($n == 0)
			continue;

		if($n > 0) // Открывающихся больше закрывающихся
		{
			$text .= str_repeat("[/$tag]", $n);
			continue;
		}

		// Закрывающихся больше открывающихся
		$text = str_repeat("[$tag]", -$n) . $text;
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

function bors_text_clear($text, $morfology = true, $spacer = ' ', $lowercase = true)
{
	if($morfology)
		require_once('classes/inc/text/Stem_ru.php');

	$text = str_replace(
		['«','»','№', '>'],
		[' ',' ',' ', '> '],
		$text);

	$text = strip_tags($text);

	$text = preg_replace(
		['/&\w+;/', '/&#\d+;/', "![\x01-, :-@ [-` {-~]!x", '/[^\w\.\-]/u'],
		[' ', ' ', ' ', ' '],
		$text);

	$result = trim(preg_replace('/\s\s+/', ' ', $text));
	if($lowercase)
		$result = bors_lower($result);

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

function clause_truncate_ceil($text, $limit, $max_limit = NULL)
{
	if(is_null($max_limit))
		$max_limit = $limit * 2;

	if(preg_match("/^(.{".$limit."}.+?[\.\?!])(\n|\s|$)/su", $text, $m))
		if(bors_strlen($m[1]) <= $max_limit)
			return $m[1];

	return truncate($text, $max_limit, ec('…'));
}
