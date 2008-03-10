<?
    function lst_news($txt)
    {
        list($url, $title, $text) = split("\|",$txt."||");
        if($url)
            $title="<a href=\"$url\">$title</a>";
        return "<dl class=\"box\"><dt>$title</dt><dd>$text<div align=\"right\"><small><a href=\"$url\">дальше...</a></small></div></dd></dl>\n";
    }
?>