<?
    function lcml_pure_mail($txt)
    {
		$mail_chars = 'a-zA-Z0-9\_\-\+\.';
        $txt=preg_replace("!(\s+|^|\])([$mail_chars]+@[$mail_chars]+)(\s+|$|\[|;|\.|:)!ime", "'$1'.mask_email('$2').'$3'", $txt);

//		echo "<xmp>$txt</xmp>";

        return $txt;
    }

	function mask_email($email)
	{
		list($user, $domain) = split('@', $email);
		$rev = "";
		for($i=strlen($email)-1; $i>=0; $i--)
			$rev .= $email[$i];
		
		return "<script>document.write('<a href='+'\"'+'ma'+'i'+'lto'+':'+'".addslashes($rev)."'.split('').reverse().join('')+'\">')</script>$user<span style=\"color: red;\"><img src=\"http://balancer.ru/img/rt.gif\" width=\"16\" height=\"16\" border=\"0\" align=\"absmiddle\"/></span>$domain<script>document.write('</'+'a>')</script>";
	}
