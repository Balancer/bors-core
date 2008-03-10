<?
/*    function lst_link($txt)
    {
        list($url,$img,$title,$text,$author,$time)=split("\|",$txt."||||||");
        if(preg_match("!^\d+$!",$time))
            $time=strftime("%d.%m.%Y %H:%M",$time);
        $img=$img?"<td width=204><a href=$url>[img $img nohref 200x]</a></td>":"";
        return "<div class=\"box\"><table width=100%><tr>$img<td><b><a href=$url>$title</a></b><p>$text</td></tr></table></div>\n";
//  $::params.="&image[]=$::image_flag{upload}";
    }*/

/*    function lcml_box($txt, $params)
    {
        my $align=$_[0]{align};
        my $width=$_[0]{width}?$_[0]{width}:1;
        return "<table border=0 width=$width cellPadding=8 cellSpacing=0 align=$align><tr><td><div id=box>".ubb_code($_[1])."</div></td></tr></table>\n";
    }
*/

    function lsp_box($txt, $params)
    {
        $class="box";
        if(preg_match("!noborder(.+?)\s*$!", $params,$m))
        {
            $class  = "nbox";
            $params = $m[1];
        }
        return "<div class=\"$class\"><table cellSpacing=\"0\" width=\"100%\">".($params?"<caption>$params</caption>":"")."<tr><td>\n$txt</td></tr></table></div>\n";
    }

?>