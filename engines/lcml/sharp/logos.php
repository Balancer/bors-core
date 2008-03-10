<?
    function lsp_logos($txt,$params)
    {
        $w=''; $h='';
        if(preg_match("!^\s*(\d*)\s*,\s*(\d*)\s*$!s",$params,$m))
        {
            $w=$m[1];
            $h=$m[2];
        }

        $res='';
        if(!isset($GLOBALS['logos_included']))
        {
            $GLOBALS['logos_included']=1;
            $res.="<script charset=\"UTF-8\" src=\"http://airbase.ru/js/logos.js\"></script>\n";
        }
        $res.="<script charset=\"UTF-8\">begLogos($w".($h?",":"")."$h)</script><noscript><ul></noscript>\n";

        foreach(split("\n",$txt) as $s)
        {
            preg_match("!^(#logitm\s+)?(.*?)\|(.*?),(.*?),(.*)$!",$s,$m);
            list($name,$url,$img,$desc)=array($m[2],$m[3],$m[4],$m[5]);
            $desc=str_replace("\"","\\\"",$desc);
            $res.="<script charset=\"UTF-8\">logoItem(\"$name\",\"$url\",\"$img\",\"$desc\")</script><noscript><li><a href=$url>$name</a> - $desc</noscript>\n";
        }

        return "$res<script charset=\"UTF-8\">endLogos()</script><noscript></ul></noscript>\n";
    }
?>