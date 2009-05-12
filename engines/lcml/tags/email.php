<?php

function lp_email($email)
{
	list($user, $domain) = explode('@', $email);
	$rev = "";
	for($i=strlen($email)-1; $i>=0; $i--)
		$rev .= $email[$i];

	return "<script>document.write('<a href='+'\"'+'ma'+'i'+'lto'+':'+'".addslashes($rev)."'.split('').reverse().join('')+'\">')</script>$user<span style=\"color: red;\"><img src=\"/_bors/i/rt.gif\" width=\"16\" height=\"16\" border=\"0\" align=\"absmiddle\"/></span>$domain<script>document.write('</'+'a>')</script>";
}
