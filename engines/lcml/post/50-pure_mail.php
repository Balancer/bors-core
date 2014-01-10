<?php
    function lcml_pure_mail($txt)
    {
		$mail_chars = 'a-zA-Z0-9\_\-\+\.';
		if(config('lcml_email_nomask'))
	        return preg_replace("!(^|[\s\]])([$mail_chars]+@[$mail_chars]+)([\s\[;\.:]|$)!im", "$1<a href=\"mailto:$2\">$2</a>$3", $txt);
		else
	        return preg_replace_callback("!(^|[\s\]])([$mail_chars]+@[$mail_chars]+)([\s\[;\.:]|$)!im",
	        	function($m) { return $m[1].mask_email($m[2], !config('lcml_email_nomask')).$m[3];}, $txt);
    }

	function mask_email($email, $img_mask = true, $text = NULL)
	{
		list($user, $domain) = explode('@', $email);
		$rev = "";
		for($i=strlen($email)-1; $i>=0; $i--)
			$rev .= $email[$i];

		if(!$text)
			$text = $user.($img_mask ? "<span style=\"color: red;\"><img src=\"/_bors/i/rt.gif\" width=\"16\" height=\"16\" align=\"absmiddle\"/></span>" : "<span>&#64;</span>")
			.$domain;

		return save_format("<script type=\"text/javascript\"><!--\ndocument.write('<a href='+'\"'+'ma'+'i'+'lto'+':' +'".addslashes($rev)."'.split('').reverse().join('') +'\">')\n--></script>{$text}<script type=\"text/javascript\"><!--\ndocument.write('</'+'a>')\n--></script>");
	}
