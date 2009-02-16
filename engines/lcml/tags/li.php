<?
	function lt_li($text)
	{
		return "<li />";
	}

    function lp_li($text)
    {
        return save_format("<li>".lcml($text)."</li>\n");
    }

    function lp_ul($text, $param)
    {
		if($param['orig'])
			$type = " type=\"".htmlspecialchars($param['orig'])."\"";
		else
			$type = "";

        return save_format("<ul$type>\n".lcml($text)."</ul>\n");
    }

    function lp_ol($text, $param)
    {
		if($param['orig'])
			$type = " type=\"".htmlspecialchars($param['orig'])."\"";
		else
			$type = "";
			
        return save_format("<ol$type>\n".lcml($text)."</ol>\n");
    }

require_once('inc/strings.php');
function lp_list($text)
{
	return preg_replace('/^\[\*\](.*?)$/me', "'<li>'.lcml(stripq('$1')).'</li>'", $text);
}
