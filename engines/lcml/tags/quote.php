<?
    function lp_quote($txt, $params)
    {
		if(empty($params['description']))
			$out = " <blockquote>";
		else
			$out = " <blockquote><small><b><div class=\"quotetop\" style=\"border-bottom-width: 1px; border-bottom-style: solid;\">{$params['description']}</div></b></small>";
		return $out.lcml(trim($txt), array('html'=>'safe'))."</blockquote> ";
    }
?>