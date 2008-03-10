<?
	function lt_li($text)
	{
		return "<li />";
	}

    function lp_li($text)
    {
        return "<li>".lcml($text)."</li>\n";
    }

    function lp_ul($text, $param)
    {
		if($param['orig'])
			$type = " type=\"".htmlspecialchars($param['orig'])."\"";
		else
			$type = "";

        return "<ul$type>\n".lcml($text)."</ul>\n";
    }

    function lp_ol($text, $param)
    {
		if($param['orig'])
			$type = " type=\"".htmlspecialchars($param['orig'])."\"";
		else
			$type = "";
			
        return "<ol$type>\n".lcml($text)."</ol>\n";
    }
