<?
	function lt_li($text)
	{
		return "<li />";
	}

    function lp_li($text)
    {
        return "<li>".lcml($text)."</li>---save_cr---";
    }

    function lp_ul($text, $param)
    {
		if($param['orig'])
			$type = " type=\"".htmlspecialchars($param['orig'])."\"";
		else
			$type = "";

        return "<ul$type>---save_cr---".lcml($text)."</ul>---save_cr---";
    }

    function lp_ol($text, $param)
    {
		if($param['orig'])
			$type = " type=\"".htmlspecialchars($param['orig'])."\"";
		else
			$type = "";
			
        return "<ol$type>---save_cr---".lcml($text)."</ol>---save_cr---";
    }
