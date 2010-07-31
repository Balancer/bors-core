<?php
    function lcml_old_bb($txt)
    {
		$txt = preg_replace("!\n*\[list\](.+?)\[/list\]\n*!is", "[ul]$1[/ul]", $txt);
		$txt = preg_replace("!^\[\*\](.+?)$!ime", "'[li]'.stripslashes(trim('$1')).'[/li]'", $txt);

		$txt = preg_replace("!\[size=(\d+)\](.+?)\[/size\]!is", "[html_font size=$1]$2[/html_font]", $txt);

		$txt = preg_replace("!\[email=(.+?)\](.+?)\[/email\]!ie", "mask_email('$1', ".(config('lcml_email_nomask') ? 'false' : 'true').", '$2')", $txt);

		return $txt;
	}
