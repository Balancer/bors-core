<?php

function lcml_html_pre($txt)
{
	if(config('lcml_html_nonmutable'))
	    return $txt;

//	$txt = "html_disable = {$GLOBALS['lcml']['params']['html_disable']} - $txt";

		$txt = preg_replace('/<!\-\-.*?\-\->/', '', $txt);

//		print_d($GLOBALS['lcml']['params']['html_disable']);
		if(empty($GLOBALS['lcml']['params']['html_disable']))
			return $txt;

//		foreach(array('&raquo;' => '»', '&laquo;' => '«', '&mdash;' => '—') as $from => $to)
//			$txt = str_replace($from, $to, $txt);

//		echo "***:{$GLOBALS['lcml']['params']['html_disable']}";

		if("".$GLOBALS['lcml']['params']['html_disable'] == 'full')
			return str_replace('&amp;', '&', htmlspecialchars($txt));

//		echo "***";

//		$txt = preg_replace("!</p>!","", $txt);
//		$txt = preg_replace("!<p>!","<br /><br />", $txt);
		$txt = preg_replace("!<tr!","<tabtr", $txt);
		$txt = preg_replace("!</tr!","</tabtr", $txt);

		$txt = preg_replace("!<img src=(http://[^> ]+)>!","<html_img src=\"$1\">", $txt);
		$txt = preg_replace("!<img !","<html_img ", $txt);
		$txt = preg_replace("!<a !","<html_a ", $txt);
		$txt = preg_replace("!</a>!","</html_a>", $txt);

		foreach(explode(' ', 'font iframe') as $tag)
		{
			$txt = preg_replace("!<$tag>!i","[html_$tag]", $txt);
			$txt = preg_replace("!<$tag\s*/>!i","[html_$tag]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)\s*/>!i","[html_$tag $1]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)>!i","[html_$tag $1]", $txt);
			$txt = preg_replace("!</$tag>!i","[/html_$tag]", $txt);
		}

		foreach(explode(' ','param') as $tag)
		{
			$txt = preg_replace("!<$tag\s+([^>]+)>\s*</$tag>!is","[$tag $1]", $txt);
		}

		// Строго парные тэги
		foreach(explode(' ','embed') as $tag)
		{
			$txt = preg_replace("!<$tag\s+([^>]+)>(.*?)</$tag>!is","[$tag $1]$2[/$tag]", $txt);
		}

		// Ошибочные парные тэги, повторённые один раз. Остаток после предыдущего выправления.
		foreach(explode(' ','embed') as $tag)
		{
			$txt = preg_replace("!<$tag\s+([^>]+)>!is","[$tag $1][/$tag]", $txt);
		}

		// Парные тэги, прямо транслирующиеся в BB-код:
		foreach(explode(' ','style tt sub sup code quote') as $tag)
		{
			$txt = preg_replace("!<$tag>(.+?)</$tag>!is","[$tag]$1[/$tag]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)>(.+?)</$tag>!is","[$tag $1]$2[/$tag]", $txt);
		}

		foreach(explode(' ','b big br center code div font h1 h2 h3 h4 hr i li object p param pre s small span strong u ul xmp tabtr table td th html_img html_a') as $tag)
		{
			$txt = preg_replace("!<$tag>!i","[$tag]", $txt);
			$txt = preg_replace("!<$tag\s*/>!i","[$tag]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)\s*/>!i","[$tag $1]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)>!i","[$tag $1]", $txt);
			$txt = preg_replace("!</$tag>!i","[/$tag]", $txt);
		}

		foreach(array("\"", "'", "") as $q)
		{
			$mask = $q ? "^$q" : "^\s>";
			$txt = preg_replace("!<img [^>]*src=$q([$mask]+){$q}[^>]*?>!is", "[img]$1[/img]", $txt);
			$txt = preg_replace("!<a [^>]*href=$q([$mask]+){$q}[^>]*>(.*?)</a>!is", "[url=$1]$2[/url]", $txt);
		}

		return config('lcml_html_special_chars_enable') ? $txt : htmlspecialchars($txt, ENT_NOQUOTES);
    }
