<?
    function lcml_pure_mail($txt)
    {
		$mail_chars = 'a-zA-Z0-9\_\-\+\.';
		if(config('lcml_email_nomask'))
	        $txt=preg_replace("!(\s+|^|\])([$mail_chars]+@[$mail_chars]+)(\s+|$|\[|;|\.|:)!ime", "'$1'.mask_email('$2', false).'$3'", $txt);
		else
	        $txt=preg_replace("!(\s+|^|\])([$mail_chars]+@[$mail_chars]+)(\s+|$|\[|;|\.|:)!ime", "'$1'.mask_email('$2', true).'$3'", $txt);

//		echo "<xmp>$txt</xmp>";

        return $txt;
    }

	function mask_email($email, $img_mask = true)
	{
		list($user, $domain) = explode('@', $email);
		$rev = "";
		for($i=strlen($email)-1; $i>=0; $i--)
			$rev .= $email[$i];
		
		return "<script type=\"text/javascript\">document.write('<a href='+'\"'+'ma'+'i'+'lto'+':' +'".addslashes($rev)."'.split('').reverse().join('') +'\">')</script>$user"
			.($img_mask ? "<span style=\"color: red;\"><img src=\"http://balancer.ru/img/rt.gif\" width=\"16\" height=\"16\" align=\"absmiddle\"/></span>" : "<span>&#64;</span>")
			."$domain<script type=\"text/javascript\">document.write('</'+'a>')</script>";
	}
