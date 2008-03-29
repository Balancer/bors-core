<?
    function lcml_html_pre($txt)
    {
//		$txt = "html_disable = {$GLOBALS['lcml']['params']['html_disable']} - $txt";

		$txt = preg_replace('/<!\-\-.*?\-\->/', '', $txt);

		if(empty($GLOBALS['lcml']['params']['html_disable']))
			return $txt;
		
//		$txt = preg_replace("!</p>!","", $txt);
//		$txt = preg_replace("!<p>!","<br /><br />", $txt);
		$txt = preg_replace("!<tr!","<tabtr", $txt);
		$txt = preg_replace("!</tr!","</tabtr", $txt);

		$txt = preg_replace("!<img !","<html_img ", $txt);
		$txt = preg_replace("!<a !","<html_a ", $txt);
		$txt = preg_replace("!</a>!","</html_a>", $txt);

		foreach(explode(' ', 'font') as $tag)
		{
			$txt = preg_replace("!<$tag>!i","[html_$tag]", $txt);
			$txt = preg_replace("!<$tag\s*/>!i","[html_$tag]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)\s*/>!i","[html_$tag $1]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)>!i","[html_$tag $1]", $txt);
			$txt = preg_replace("!</$tag>!i","[/html_$tag]", $txt);
		}

		foreach(split(' ','param') as $tag)
		{
			$txt = preg_replace("!<$tag\s+([^>]+)></$tag>!","[$tag $1]", $txt);
		}
	
		foreach(split(' ','b big br center code div embed font h1 h2 h3 h4 hr i li object p param pre s small span strong u ul xmp tabtr table td html_img html_a') as $tag)
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
		
		return htmlspecialchars($txt, ENT_NOQUOTES);
    }
