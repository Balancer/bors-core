<?php

class bors_lib_html
{
	function get_meta_data($content) // via http://ru2.php.net/get_meta_tags
	{
		$content = preg_replace("'<style[^>]*>.*</style>'siU",'',$content);  // strip js
		$content = preg_replace("'<script[^>]*>.*</script>'siU",'',$content); // strip css
		$meta = array();
		foreach(explode("\n", $content) as $s)
			if(preg_match('!<meta[^>]+(http\-equiv|name)=[\'"]([^\"\']*)[\'"][^>]+(content|value)=[\"\']([^\"\']*)[\'\"]!i', trim($s), $m))
				$meta[$m[2]] = html_entity_decode(html_entity_decode($m[4], ENT_COMPAT, 'UTF-8'));

		return $meta;
	}
}
