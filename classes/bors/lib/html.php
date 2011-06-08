<?php

class bors_lib_html
{
	function get_meta_data($content) // via http://ru2.php.net/get_meta_tags
	{
		$content = preg_replace("'<style[^>]*>.*</style>'siU",'',$content);  // strip js
		$content = preg_replace("'<script[^>]*>.*</script>'siU",'',$content); // strip css

		$meta = array();

		foreach(explode("\n", $content) as $s)
			if(preg_match("!<link rel=\"([^\"]+)\" href=\"([^\"]+)\" />!i", trim($s), $m))
				$meta[$m[1]] = $m[2];

		$content = str_replace("\n", " ", $content);
		$content = preg_replace("!<meta !", "\n<meta ", $content);
		$content = preg_replace("!/>!", "/>\n", $content);
//print_dd($content);
		foreach(explode("\n", $content) as $s)
		{
//			echo "<xmp>=$s=</xmp>";
			if(preg_match("!<meta[^>]+name=\"(\w+)\"[^>]+(content|value)=\"(.*?)\"!is", trim($s), $m))
				$meta[$m[1]] = html_entity_decode(html_entity_decode($m[3], ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8');

			if(preg_match("!<meta[^>]+(http\-equiv|name)=['\"](\w+)['\"][^>]+(content|value)='([^']*)'!is", trim($s), $m))
				$meta[$m[2]] = html_entity_decode(html_entity_decode($m[4], ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8');
		}


		if(empty($meta['title']) && preg_match('!<title>([^>]+)</title>!si', $content, $m))
			$meta['title'] = html_entity_decode($m[1], ENT_COMPAT, 'UTF-8');

		return $meta;
	}
}
