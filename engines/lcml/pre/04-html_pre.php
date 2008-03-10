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

		foreach(split(' ','param') as $tag)
		{
			$txt = preg_replace("!<$tag\s+([^>]+)></$tag>!","[$tag $1]", $txt);
		}
	
		foreach(split(' ','b big br center code div embed font hr i li object p param pre s small span strong u ul xmp tabtr table td html_img html_a') as $tag)
		{
			$txt = preg_replace("!<$tag>!","[$tag]", $txt);
			$txt = preg_replace("!<$tag\s*/>!","[$tag]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)\s*/>!","[$tag $1]", $txt);
			$txt = preg_replace("!<$tag\s+([^>]+)>!","[$tag $1]", $txt);
			$txt = preg_replace("!</$tag>!","[/$tag]", $txt);
		}

		foreach(array("\"", "'", "") as $q)
		{
			$mask = $q ? "^$q" : "^\s>";
			$txt = preg_replace("!<img [^>]*src=$q([$mask]+){$q}[^>]*?>!is", "[img]$1[/img]", $txt);
			$txt = preg_replace("!<a [^>]*href=$q([$mask]+){$q}[^>]*>(.*?)</a>!is", "[url=$1]$2[/url]", $txt);
		}
		
		return htmlspecialchars($txt, ENT_NOQUOTES);
    }
