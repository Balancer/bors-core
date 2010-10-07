<?php
	function lcml_images($txt)
	{
		if(lcml_tag_disabled('img'))
			return $txt;

		$n=50;
		while(preg_match("!\[([http://\w\.\-\+%_/:&\?=#]+\.(jpg|jpeg|gif|png|sjpg))([^\]]*)\]!ie", $txt, $m) && $n-->0)
			$txt = str_replace($m[0], lcml("[img \"{$m[1]}\" noflow {$m[3]}]"), $txt);

		$n=50;
		while(preg_match("!(^|\s)(http://\S+\.(jpg|png|gif|jpeg|sjpg))(?=($|\s))!ime", $txt, $m) && $n-->0)
			$txt = str_replace($m[0], $m[1].lcml("[img \"{$m[2]}\" left noflow|Взято [url \"{$m[2]}\"|тут]]"), $txt);

		$n=50;
		while(preg_match("!\[([\w/]+.(jpg|jpeg|gif|png|sjpg))([^\]]*)\]!ie", $txt, $m) && $n-->0)
			$txt = str_replace($m[0], lcml("[img \"{$m[1]}\" noflow {$m[3]}]"), $txt);

		return $txt;
	}
