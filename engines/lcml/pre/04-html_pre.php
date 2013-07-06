<?php

function lcml_html_pre($txt)
{
//	$txt = preg_replace('!<br\s*/>!', "\n", $txt);
	if(config('lcml_html_nonmutable'))
	    return $txt;

//	$txt = "html_disable = {$GLOBALS['lcml']['params']['html_disable']} - $txt";

		$txt = preg_replace('/<!\-\-[^#].*?\-\->/', '', $txt);

//		print_d($GLOBALS['lcml']['params']['html_disable']);
		if(empty($GLOBALS['lcml']['params']['html_disable']))
			return $txt;

//		foreach(array('&raquo;' => '»', '&laquo;' => '«', '&mdash;' => '—') as $from => $to)
//			$txt = str_replace($from, $to, $txt);

//		echo "***:{$GLOBALS['lcml']['params']['html_disable']}";

		if("".$GLOBALS['lcml']['params']['html_disable'] == 'full')
			return str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $txt);

//		echo "***";

		$txt = preg_replace('!<script.*/script.*?>!i', '[warning]Javascript disabled[/warning]', $txt);

//		$txt = preg_replace("!</p>!","", $txt);
//		$txt = preg_replace("!<p>!","<br /><br />", $txt);
		$txt = preg_replace("!<tr!","<tabtr", $txt);
		$txt = preg_replace("!</tr!","</tabtr", $txt);

		$txt = preg_replace("!<img src=(http://[^> ]+)>!","<html_img src=\"$1\">", $txt);
//		$txt = preg_replace("!<img src=\"(http://[^\"]+)\"[^>]+>!","<html_img src=\"$1\">", $txt);
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

		// Строго парные теги
		foreach(explode(' ','em embed nobr strike') as $tag)
		{
			$txt = preg_replace("!<$tag>(.*?)</$tag>!is","[$tag]$1[/$tag]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)>(.*?)</$tag>!is","[$tag $1]$2[/$tag]", $txt);
		}

		// Строго парные теги. отображаемые в другие BB-теги
		foreach(array('blockquote' => 'quote', 'table' => 'table_html') as $html_tag => $bb_tag)
		{
			$txt = preg_replace("!<$html_tag>(.*?)</$html_tag>!is","[$bb_tag]$1[/$bb_tag]", $txt);
			$txt = preg_replace("!<$html_tag\s+([^>]+)>(.*?)</$html_tag>!is","[$bb_tag $1]$2[/$bb_tag]", $txt);
		}

		// Ошибочные парные теги, повторённые один раз. Остаток после предыдущего выправления.
		foreach(explode(' ','embed') as $tag)
		{
			$txt = preg_replace("!<$tag\s+([^>]+)>!is","[$tag $1][/$tag]", $txt);
		}

		// Парные теги, прямо транслирующиеся в BB-код:
		foreach(explode(' ','form style tt sub sup code quote') as $tag)
		{
			$txt = preg_replace("!<$tag>(.+?)</$tag>!is","[$tag]$1[/$tag]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)>(.+?)</$tag>!is","[$tag $1]$2[/$tag]", $txt);
		}

		// Строго одиночные теги
		foreach(explode(' ','input') as $tag)
		{
			$txt = preg_replace("!<$tag\s+([^>]+)>!is","[$tag $1]", $txt);
		}

		foreach(explode(' ','b big br center code div font h1 h2 h3 h4 hr i li object p param pre s small span strong u ul xmp tabtr tbody td th thead html_img html_a') as $tag)
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

		// Придумать, как обойти порчу ссылок.
		// Видимо, ставить в post? config('lcml_html_special_chars_enable') ? $txt : htmlspecialchars($txt, ENT_NOQUOTES);
		return preg_replace('/([^\-])>/', '$1&gt;', $txt);
    }
