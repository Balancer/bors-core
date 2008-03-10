<?
    function lcml_icq($txt)
    {
        $txt=preg_replace("!ICQ#(\d+)!","<img src=\"http://wwp.icq.com/scripts/online.dll?icq=$1&img=5\" width=\"18\" height=\"18\" align=\"absmiddle\">$1",$txt);
        $txt=preg_replace("!(\d+)#ICQ!","$1<img src=\"http://wwp.icq.com/scripts/online.dll?icq=$1&img=5\" width=\"18\" height=\"18\" align=\"absmiddle\">",$txt);
        return $txt;
    }
?>