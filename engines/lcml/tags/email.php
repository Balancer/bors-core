<?php

function lp_email($text, $params)
{
	extract($params);
	if(empty($email))
		$email = @$text;

	list($user, $domain) = explode('@', $email);
	$rev = "";
	for($i=strlen($email)-1; $i>=0; $i--)
		$rev .= $email[$i];

	if($text == $email)
		$text = "$user<span style=\"color: red;\"><img src=\"/_bors/i/rt.gif\" width=\"16\" height=\"16\" border=\"0\" align=\"absmiddle\"/></span>$domain";

	return save_format("<script type=\"text/javascript\"><!--\ndocument.write('<a href='+'\"'+'ma'+'i'+'lto'+':'+'".addslashes($rev)."'.split('').reverse().join('')+'\">')\n--></script>{$text}<script type=\"text/javascript\"><!--\ndocument.write('</'+'a>')\n--></script>");
}
